Save
======
2017-08-31 -> 2017-09-05


This follows up and supersedes the **save-2017-08-31.md** document.




It's all about convenience for the client developer (us).


Motivation
==============

Why change the save system? what was wrong with the old system?


With the new system presented below, given a table with a primary key AND unique indexes,
it is easy to lead the SaveOrm to trigger either an update using the primary key to update unique indexes, or, 
an update using unique indexes to update primary key.
 
Imagine the following table with both a primary key and one unique index:

- user
    - id: pk 
    - email: uk 
    - gender: 1 
 

Then, the new system allows us to do both requests easily without ambiguity:
 
```php
update user set email='aaa' where id=6
update user set id=7 where email='aaa'
```






The save method behaviour: how to use SaveOrm
===========================

When we associate multiple objects together and then call the save method, to fully understand how save work
means understanding two things:

- how relationships are prioritized 
- how saving one object work


Relationships priorities
--------------------

Here is what the ObjectManager (the object that actually save all objects) prioritize relationships compared
to the originalObject:


- save siblings first (as their "results" are injected into the originalObject)
- save originalObject (the "result" of the original object is now available)
- save bindings after the originalObject (as the original object's result is only now available)
- save children after the originalObject (as the original object's result is only now available)
 


Saving one object
--------------------

An important question that occurs inside the ObjectManager when it comes to saving an object
is whether it should try to insert into the database, or update an existing record in the database.


What would you do with the following code: insert or update?

```php
ProductCardLangObject::create()
    ->setSlug("the_card_slug")
    ->setLang($lang)
    ->setLabel("the card label")
    ->save($savedResults);
```


The section below explains how SaveOrm handles this problem.

What's important to understand, and that is not said elsewhere except for the source code,
is that the ObjectManager uses 2 different modes:

- insert mode
- update mode


In insert mode, the saving is an insert operation.
In update mode, we first try to fetch whether or not the record exist, and if it does it's an update operation,
or if it fails we try the insert operation.

In both cases, a failing insert throws an exception: there is no quiet mode, or ignore failure mode,
as SaveOrm considers that allowing such a mode would lead to undesirable behaviour
(this might change in the future).
 
 
### How do we trigger those modes?

By default, when we create an object, it's always in insert mode.


The following object is in insert mode:

```php
ProductCardLangObject::create()
    ->setSlug("the_card_slug")
    ->setLang($lang)
    ->setLabel("the card label")
    ->save();
```


The **ONLY WAY** to trigger update mode is to call a createByXXX method.

The following object is in update mode: 

```php
ProductCardLangObject::createBySlugLangId("the_card_slug", $lang->getId())
    ->setLabel("the card label")
    ->save();
```


It's worth knowing that the createByXXX methods are based on the following set of properties:

- primary key
- unique indexes
- ric (row identifying fields, defined manually by the client developer)



### What does it means in practice?

In practice, let's examine the following code:

```php
ProductCardLangObject::create()
    ->setSlug("the_card_slug")
    ->setLang($lang)
    ->setLabel("the card label")
    ->save();
```

This code is in insert mode.
It will fail "only" if a duplicate error occurs, which means if the unique index (slug in this case)
already exists in the database (in which case an exception is thrown).

Now, what if we use the update code?

```php
ProductCardLangObject::createBySlugLangId("the_card_slug", $lang->getId())
    ->setLabel("the card label")
    ->save();
```

The above code is in update mode.
It will try to fetch the record with slug="the_card_slug" and lang_id=$lang->getId().
If such a record exist, the update operation will occur, using the slug and lang_id
in its where clause.

If the record doesn't exist, it falls back to the insert mode, exactly like in the first code example,
which means it either succeeds, or fails (duplicate error) and throws an exception.

 
 
 
 
So, that's it.
Hopefully this approach makes sense and you can get accustomed to it.
 










