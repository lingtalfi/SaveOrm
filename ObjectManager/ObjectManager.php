<?php


namespace SaveOrm\ObjectManager;


use Bat\CaseTool;
use OrmTools\Helper\OrmToolsHelper;
use QuickPdo\QuickPdo;
use QuickPdo\QuickPdoStmtTool;
use SaveOrm\Exception\SaveException;
use XiaoApi\Helper\QuickPdoStmtHelper\QuickPdoStmtHelper;

class ObjectManager
{


    // contextual
    private $_savedObject;
    private $_saveResults;


    public function __construct()
    {
        $this->_saveResults = [];
    }


    public static function create()
    {
        return new static();
    }


    /**
     * see save.md for details about this method
     */

    public function save(\SaveOrm\Object\Object $object)
    {
        $ret = null;
        $this->_saveResults = [];
        $this->_savedObject = $object;
        QuickPdo::transaction(function () use ($object, &$ret) {
            $ret = $this->doSave($object);
        }, function (\Exception $e) {
//            a($this->_saveResults);
            throw $e;
        });

        return $ret;
    }


    /**
     * @return array
     */
    public function getSaveResults()
    {
        return $this->_saveResults;
    }



    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * @return array
     */
    protected function getGeneralConfig()
    {
        return [];
    }


    //--------------------------------------------
    //
    //--------------------------------------------
    /**
     * I use a separate doSave method to avoid nested transactions problem.
     * This means internally, every save is operated with the doSave method.
     * The save method is just for the first call.
     */
    private function doSave(\SaveOrm\Object\Object $object)
    {
        $info = $this->getInstanceInfo($object);
        $generalConfig = $this->getGeneralConfig();
        $table = $info['table'];
        $properties = $info['properties'];
        $foreignKeys = $info['fks'];
        $ai = $info['ai'];
        $uniqueIndexes = $info['uniqueIndexes'];
        $primaryKey = $info['primaryKey'];
        $ric = (array_key_exists('ric', $info)) ? $info['ric'] : [];
        $bindings = (array_key_exists('bindings', $info)) ? $info['bindings'] : [];
        $childrenTables = (array_key_exists('childrenTables', $info)) ? $info['childrenTables'] : [];
        $allPrefixes = (array_key_exists('tablePrefixes', $generalConfig)) ? $generalConfig['tablePrefixes'] : [];
        $managerInfo = $object->_getManagerInfo();


        //--------------------------------------------
        // SIBLINGS FIRST
        //--------------------------------------------
        foreach ($foreignKeys as $foreignKey => $fkInfo) {
            $siblingTable = $fkInfo[0];
            $siblingColumn = $fkInfo[1];
            $siblingGetMethod = $this->getMethodByTable('get', $siblingTable, $allPrefixes);

            if (method_exists($object, $siblingGetMethod)) {
                $siblingObject = $object->$siblingGetMethod();
                if (null !== $siblingObject) {
                    $siblingRet = $this->doSave($siblingObject);
                    $this->_saveResults[$siblingTable] = $siblingRet;

                    // setting back the sibling value into the source object
                    $getter = $this->getMethodByProperty('get', $siblingColumn);
                    $setter = $this->getMethodByProperty('set', $foreignKey);

                    $siblingValue = $siblingObject->$getter();
                    $object->$setter($siblingValue);

                }
            }
        }


        //--------------------------------------------
        // CHECKING VALUES
        //--------------------------------------------
        $values = [];
        foreach ($properties as $prop) {

            $pascal = $this->getPascal($prop);
            $method = 'get' . $pascal;
            $value = $object->$method();

            // unresolved foreign keys?
            if (true === $this->isForeignKey($prop, $foreignKeys) && null === $value) {
                $this->saveError("Unresolved foreign key for table $table, column $prop");
            }
            $values[$prop] = $value;
        }


        /**
         * At this point, values has been resolved and verified,
         * we can now safely record to the database
         */
        //--------------------------------------------
        // CREATE OR UPDATE?
        //--------------------------------------------
        $isCreate = ('insert' === $managerInfo['mode']);
        $whereSuccess = $managerInfo['whereSuccess'];


        //--------------------------------------------
        // NOW SAVE
        //--------------------------------------------
        if (
            true === $isCreate ||
            (false === $isCreate && false === $whereSuccess)
        ) {
            $ret = QuickPdo::insert($table, $values);
            if (null !== $ai && false !== $ret) {
                $values[$ai] = (int)$ret;
            }
        } else {
            $where = $managerInfo['where'];


            if (null === $where) { // createUpdate
                $identifiers = $this->getMostRelevantIdentifiers($info);
                $where = array_intersect_key($values, array_flip($identifiers));
            }

            $pdoWhere = QuickPdoStmtHelper::simpleWhereToPdoWhere($where);

            // filtering values, we only update the properties that the user set manually
            $changedProps = $managerInfo['changedProperties'];
            $updateValues = array_intersect_key($values, array_flip($changedProps));
            QuickPdo::update($table, $updateValues, $pdoWhere);

        }

        //--------------------------------------------
        // RETURN VALUE
        //--------------------------------------------
        $ret = null;
        if (null !== $ai) {
            $ret = $values[$ai];
        } elseif (count($primaryKey) > 0) {
            $ret = array_intersect_key($values, array_flip($primaryKey));
        } elseif (count($uniqueIndexes) > 0) {
            $ret = array_intersect_key($values, array_flip($uniqueIndexes));
        } elseif (count($ric) > 0) {
            $ret = array_intersect_key($values, array_flip($ric));
        } else {
            $ret = $values;
        }
        $this->_saveResults[$table] = $ret;

        //--------------------------------------------
        // INJECTION (I don't remember why this is necessary)
        //--------------------------------------------
        foreach ($values as $k => $v) {
            $setter = $this->getMethodByProperty('set', $k);
            $object->$setter($v);
        }


        //--------------------------------------------
        // BINDINGS
        //--------------------------------------------
        if ($bindings) {
            foreach ($bindings as $guestLink) {
                list($guestDb, $guestLink) = explode('.', $guestLink);
                list($guestTable, $guestColumn) = $this->getLinkInfo($guestLink);
                $method = $this->getMethodByTable('get', $guestTable, $allPrefixes);


                $guestObject = $object->$method();
                if (null !== $guestObject) {

                    // pass the newly created host data to the guest object
                    $guestInfo = $this->getInstanceInfo($guestObject);
                    $fks = $guestInfo['fks'];
                    $fkInfo = $this->getForeignKeyInfoPointingTo($table, $fks, $guestTable, $guestColumn);
                    list($foreignKey, $referencedKey) = $fkInfo;


                    /**
                     * Injection, only if the value hasn't been set manually (or by using a createByXXX method)
                     */
                    $guestInfo = $guestObject->_getManagerInfo();
                    $guestChangedProps = $guestInfo['changedProperties'];
                    if (!in_array($foreignKey, $guestChangedProps)) {
                        $getMethod = $this->getMethodByProperty('get', $referencedKey);
                        $setMethod = $this->getMethodByProperty('set', $foreignKey);
                        $value = $object->$getMethod();
                        $guestObject->$setMethod($value);
                    }

                    $ret2 = $this->doSave($guestObject);
                    $this->_saveResults[$guestTable] = $ret2;
                }
            }
        }


        //--------------------------------------------
        // CHILDREN RELATIONSHIP
        //--------------------------------------------
        // right table
        //--------------------------------------------
        foreach ($childrenTables as $childrenItem) {
            list($rightTable, $middleLeftForeignKey, $middleRightForeignKey) = $childrenItem;

            $rightTablePlural = OrmToolsHelper::getPlural($rightTable);
            $accessor = $this->getMethodByTable("get", $rightTablePlural, $allPrefixes);
            if (method_exists($object, $accessor)) {
                $rightObjects = $object->$accessor();
                foreach ($rightObjects as $rightObject) {

                    $ret2 = $this->doSave($rightObject);
                    $this->_saveResults[$rightTable] = $ret2;


                    $middleObject = $rightObject->_has_;

                    $middleInfo = $this->getInstanceInfo($middleObject);
                    $middleFKeys = $middleInfo['fks'];
                    $middleTable = $middleInfo['table'];


                    // left injection on middle object
                    if (array_key_exists($middleLeftForeignKey, $middleFKeys)) {
                        $col = $middleFKeys[$middleLeftForeignKey][1];
                        $getMethod = $this->getMethodByProperty('get', $col);
                        $setMethod = $this->getMethodByProperty('set', $middleLeftForeignKey);
                        $value = $object->$getMethod();
                        $middleObject->$setMethod($value);
                    } else {
                        $this->saveError("Invalid middleLeftForeignKey: $middleLeftForeignKey");
                    }


                    // right injection on middle object
                    if (array_key_exists($middleRightForeignKey, $middleFKeys)) {
                        $col = $middleFKeys[$middleRightForeignKey][1];
                        $getMethod = $this->getMethodByProperty('get', $col);
                        $setMethod = $this->getMethodByProperty('set', $middleRightForeignKey);
                        $value = $rightObject->$getMethod();
                        $middleObject->$setMethod($value);
                    } else {
                        $this->saveError("Invalid middleLeftForeignKey: $middleRightForeignKey");
                    }

                    $ret2 = $this->doSave($middleObject);
                    $this->_saveResults[$middleTable] = $ret2;

                }
            }

        }


        return $ret;
    }


    private function getLinkInfo($link)
    {
        $p = explode(".", $link, 2);
        if (false === array_key_exists(1, $p)) {
            $p[1] = null;
        }
        return $p;
    }


    private function getMostRelevantIdentifiers(array $info)
    {
        if (null !== $info['ai']) {
            return $info['ai'];
        } elseif (count($info['primaryKey']) > 0) {
            return $info['primaryKey'];
        } elseif (count($info['uniqueIndexes']) > 0) {
            return $info['uniqueIndexes'];
        }
        return $info['properties'];
    }


    /**
     * @param $referencedTable
     * @param array $sourceForeignKeys
     * @param $sourceTable
     * @param $sourceColumn
     * @return array [sourceColumn, referencedColumn]
     */
    private function getForeignKeyInfoPointingTo($referencedTable, array $sourceForeignKeys, $sourceTable, $sourceColumn)
    {
        $tableFKeys = [];
        foreach ($sourceForeignKeys as $column => $fkInfo) {
            list($refTable, $refColumn) = $fkInfo;

            $ret = [$column, $refColumn];

            if ($sourceColumn === $column) {
                return $ret;
            }

            if ($refTable === $referencedTable) {
                $tableFKeys[] = $ret;
            }
        }
        $n = count($tableFKeys);

        if (1 === $n) {
            return array_shift($tableFKeys);
        } else {
            $this->saveError("Ambiguous foreign key for table $sourceTable pointing to $referencedTable. sourceColumn $sourceColumn did not match any foreign key");
        }
    }


    private function isForeignKey($key, $foreignKeys)
    {
        return array_key_exists($key, $foreignKeys);
    }

    private function saveError($msg)
    {
        $class = get_class($this->_savedObject);
        throw new SaveException("Problem with saving $class: $msg");
    }

    private function getPascal($word)
    {
        return CaseTool::snakeToFlexiblePascal($word);
    }

    /**
     * @param array $cols , array of columnName
     * @param array $values , pool of available values (k => v)
     * @param $table
     * @param $where , collects the where info for later use
     * @return bool
     */
    private function existByColumns(array $cols, array $values, $table, array &$where = [])
    {
        $where = [];
        foreach ($values as $k => $v) {
            if (in_array($k, $cols)) {
                $where[$k] = $v;
            }
        }
        $anyField = current($cols);
        $q = "select `$anyField` from " . $table;
        $markers = [];
        $pdoWhere = QuickPdoStmtHelper::simpleWhereToPdoWhere($where);
        QuickPdoStmtTool::addWhereSubStmt($pdoWhere, $q, $markers);
        $row = QuickPdo::fetch($q, $markers);
        if (false !== $row) {
            return true;
        }
        return false;
    }

    private function isCreate(array $options, array &$where = [])
    {
        $ai = $options['ai'];
        $values = $options['values'];
        $primaryKey = $options['pk'];
        $table = $options['table'];
        $ric = $options['ric'];
        $uniqueIndexes = $options['uniqueIndexes'];
        $changedProps = $options['changedProps'];


        $isCreate = true;
        if (null !== $ai) {
            if (null === $values[$ai]) {
                $isCreate = true;
                $this->registerIsCreate("ai=null", $table, $isCreate);
            } else {
                $isCreate = (false === $this->existByColumns([$ai], $values, $table, $where));
                $this->registerIsCreate("ai found", $table, $isCreate);
            }
        }

        if (true === $isCreate) {
            if (count($primaryKey) > 0) {
                $isCreate = (false === $this->existByColumns($primaryKey, $values, $table, $where));
                $this->registerIsCreate("pk found", $table, $isCreate);
            }
            if (true === $isCreate) {
                if (count($uniqueIndexes) > 0) {
                    foreach ($uniqueIndexes as $uniqueIndex) {
                        $isCreate = (false === $this->existByColumns($uniqueIndex, $values, $table, $where));
                        if (false === $isCreate) {
                            $this->registerIsCreate("uk found", $table, $isCreate);
                            break;
                        }
                    }
                }
                if (true === $isCreate) {
                    if (count($ric) > 0) {
                        $isCreate = (false === $this->existByColumns($ric, $values, $table, $where));
                        $this->registerIsCreate("ric found", $table, $isCreate);
                    }
                    if (true === $isCreate) {
                        $isCreate = (false === $this->existByColumns(array_keys($values), $values, $table, $where));
                        $this->registerIsCreate("wild record found", $table, $isCreate);
                    }
                }
            }
        }
        return $isCreate;
    }


    private function getMethodByTable($methodPrefix, $table, $tablePrefix)
    {
        if (!empty($tablePrefix)) {

            if (!is_array($tablePrefix)) {
                $tablePrefix = [$tablePrefix];
            }
            foreach ($tablePrefix as $prefix) {
                if (0 === strpos($table, $prefix)) {
                    $table = substr($table, strlen($prefix));
                    break; // only one suffix can apply
                }
            }
        }
        return $methodPrefix . $this->getPascal($table);
    }

    private function getMethodByProperty($prefix, $property)
    {
        return $prefix . $this->getPascal($property);
    }

    private function getInstanceInfo(\SaveOrm\Object\Object $object)
    {
        $class = get_class($object);
        $configClass = substr(str_replace('\\Object\\', '\\Conf\\', $class), 0, -6) . 'Conf';
        if (class_exists($configClass)) {
            return call_user_func([$configClass, 'getConf']);
        }
        throw new SaveException("No info for object $class");
    }

}