POST VIEW COUNT
===============

---------
CHANGELOG
---------
###### [RzNewsBundle](https://github.com/rzproject/NewsBundle/commit/25d5cc25a9e01e087c59d5187f6207d29bf47e18) ######
* added view count

Database initialization
-----------------------

Run the following command::

    app/console doctrine:schema:update --force

.. note::

  This will add a new field on your post table view_count
  
Implementing View Count
-----------------------

AbstractNewsController already implements ViewCountableControllerInterface and implementation of incrementPostView is already done. You just need to call $this->incrementPostView($post);
on your view_XXXXXX_Action function to trigger the count.

.. note::

  If you did not override the default RzNewsBundle controllers then all view action already triggers the incrementPostView function.
