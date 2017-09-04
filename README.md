SaveOrm
===============
2017-09-04



Generate an orm that helps you inserting/updating data in your database.





This is part of the [universe framework](https://github.com/karayabin/universe-snapshot).


Install
==========
Using the [uni](https://github.com/lingtalfi/universe-naive-importer) command.
```bash
uni import SaveOrm
```

Or just download it and place it where you want otherwise.



About the conception
===================
Please read the documents in the **doc** directory of this repository.



How to use the generator
============================


The generator conf
---------------------

The generator's behaviour is driven by the generator configuration file (aka config file).

The config file is a php file containing a **$conf** array.

Here is an example configuration file, you may find more information in the **doc** directory:


```php
<?php


$conf = [
    /**
     * The base dir defines the directory where all generated objects will reside
     */
    'baseDir' => "/tmp/class/SaveOrm/Test",
    /**
     *
     */
    'baseNamespace' => "SaveOrm\Test",
    /**
     * Which database you want to launch the generator on.
     * - array of database names
     *
     * Note: if empty, nothing will be generated
     */
    'databases' => [
        'kamille',
    ],
    /**
     * Filter the tables that the generator visits.
     * If empty, the generator visits all tables of the given database.
     *
     * - array of database_name => tables
     *      With tables:
     *              - array of allowed tables (all other tables are excluded)
     *                      - the wildcard * can be used.
     *                              For instance, "ekev_*" yields all tables
     *                              starting with the ekev_ prefix.
     *
     *
     */
    'tables' => [
//        'kamille' => [
//            'ekev_*',
//            'ekev_course',
//        ],
    ],
    /**
     * The table prefixes be used for:
     * - creating clean class names (without prefix)
     * - creating clean class methods and properties to be used by the ObjectManager
     *
     */
    'tablePrefixes' => [
        'ecc_',
        'ekev_',
        'ekfs_',
        'ektra_',
        'ek_',
        'pei_',
    ],
    /**
     * used to broadly detect children relationships (see more about children relationship in the documentation)
     * It's an array of keywords that trigger the detection of the middle table in a children relationship.
     */
    'childrenDetectionKeywords' => [
        '_has_',
    ],
    /**
     * The algorithm for children tables detection will
     * sometimes detect non middle tables as middle tables.
     *
     * Put the tables that are not middle tables here to manually "fix/workaround" the algorithm.
     *
     * It's an array of $db.$table
     *
     */
    'childrenDetectionKeywordsExceptions' => [
        'kamille.ek_shop_has_product_card_lang',
        'kamille.ek_shop_has_product_lang',
    ],
    /**
     * array of db.table => ric
     */
    'ric' => [
        'kamille.ek_shop_has_product_card_lang' => ['id'],
    ],
];
```







The generator's action
---------------------------
The generator creates the following structure:

- $baseDir:
    - Conf: (one conf object per table)
        - MyTableConf.php
        - MyTable2Conf.php
    - GeneratedObject: (one conf object per table)
        - MyTableGeneratedObject.php
        - MyTable2GeneratedObject.php
    - Object: (one conf object per table, an Object extends the corresponding GeneratedObject) 
        - MyTableObject.php
        - MyTable2Object.php
    - GeneratedBaseObject.php (this object is the parent of all GeneratedObject) 
    - GeneratedObjectManager.php  
        

Basically, you are only going to use the Object part, like so:

```php
MyTableObject::create()
->setProp1(6)
->setProp2("blabla")
->save();

```

Internally, the MyTableObject extends the MyTableGeneratedObject.

The generator will overwrite GeneratedObject and Conf object every time, but will leave alone 
the Object.

In other words, the Object zone is a safe zone for the developer. 
We can safely add methods in the Object classes.

 

The generator's code
---------------------------
Here is the code required to generate the orm structure.

```php
<?php


use Core\Services\A;
use SaveOrm\Generator\SaveOrmGenerator;

// initialize your framework (autoloader...)
require_once __DIR__ . "/../../boot.php";
require_once __DIR__ . "/../../init.php";


A::quickPdoInit(); // initialize your db object, depends on the framework you are using, I'm using kamille https://github.com/lingtalfi/kamille



// now using the generator
$gen = SaveOrmGenerator::create();

//$gen->cleanCache(); // do this when your db structure changes

$gen->setConfigFile(__DIR__ . "/../conf/saveorm-generator.conf.php") // use this config file to control the generator's behaviour
    ->generate();

``` 

 
 
 
How to use the SaveOrm orm?
===============================

First, you should have a look at the doc's explanations about relationships (the saveorm.md document inside the doc directory
of this repository).

Then, configure the generator once for all for your app.

Then launch the generator.

Then use the Object classes.

How to use the Object classes is explained below with a few examples.



Inserting/updating 
------------------------------

The insert or update operation will be chosen internally depending on
whether or not the object can do an update (if you've provided enough information
to do so).


When you create an object, the values that you don't set have a default value.

### Insert example


```php
MyTableObject::create()
->setName("maurice")
->save();
```

### Update example


```php
MyTableObject::create()
->setId(6)
->setName("maurice")
->save();
```


### Mixing insert and update

SaveOrm is smart enough to detect whether an insert or an update should be performed for each object.

When you save an ensemble of related objects (see Relationships section below),
each object is either inserted or updated, depending on the provided data for each object.


```php
MyTableObject::create()
->setId(6)
->setFlower(FlowerObject::createByName("rose")->setColor("red"))
->save();
```

In the above example, when the **save** method is called,
the MyTable object triggers an insert, while the Flower object will trigger an update.


   
   
Relationships
----------------------

Relationships in SaveOrm are special, there are three types of relationships:

- bindings
- siblings
- children

Please read the **saveorm.md** document for more info.


The benefit of relationships in general is that when you save your object,
all objects related to it will be saved too.

Plus, when you save such an ensemble of objects, some data are passed automatically
for you.


### Bindings

Use the createXXX method to define a binding between a guest object to a host object.

```php
ProductObject::create()
->setName("maurice")
->createProductLang(ProductLangObject::create()
    ->setReference("456")
    ->setPrice()
)

->save();
```

In the above example, both the Product and the ProductLang objects are saved.
   
When you save the Product object, the newly created id (or any other identifying field(s)) is inferred
automatically to the ProductLang object (i.e. you don't need to call 
the ProductLangObject.setProductId method manually). 
   
   
   
### Siblings

Use the setXXX method to define an object as a sibling.


```php
ProductObject::create()
->setName("maurice")
->setProductType(ProductTypeObject::create()
    -setName("balloon")
)

->save();
```

In the above example, both the Product and the ProductType objects are saved.
   
When you save the Product object, the newly created ProductType.id (or any other identifying field(s)) is inferred
automatically to the Product object (i.e. you don't need to call 
the Product.setProductTypeId method manually). 
   


   
### Children

Use the addXXX method to add a child to a parent.


```php
ProductObject::create()
->setName("maurice")
->addComment(CommentObject::create()
    -setText("kool"), ProductHasCommentObject::create()
)

->save();
```

In the above example, the Product, Comment and ProductHasComment objects are saved.
   
When you save the Product object, the newly created Product.id (or any other identifying field(s)) 
AND the newly created Comment.id (or any other identifying field(s))
are automatically inferred to the ProductHasComment object (i.e. you don't need to call 
the ProductHasComment.setProductId and ProductHasComment.setCommentId methods manually). 
   


CreateByXXX methods
---------------------

The generator creates createByXXX methods for every unique index it finds.

For instance if your user table has an unique index on the **email** field,
you could create the User object using the following code:


```php
UserObject::createByEmail("email@gmail.com");
```        

The returned object is suitable for an update.


Note: it also works with indexes composed of multiple keys:


```php
UserObject::createByEmailShopId("email@gmail.com", 1);
```        





Related
================

- [OrmTools](https://github.com/lingtalfi/OrmTools)





History Log
------------------    
    
- 1.1.0 -- 2017-09-04

    - the second argument of the addXXX auto-generated method is now optional 
    
- 1.0.1 -- 2017-09-04

    - fix SaveOrmGenerator algorithm for finding children
    
- 1.0.0 -- 2017-09-04

    - initial commit