# vender
high level product management functions
 - tied to a ort, need org and user perms, authentification and authorization\
 - user inputs
    - create Product
    - view products
    - delete products

- calls low level functions in a transaction, logs and records to the database

```php
public function requestNewEc2Product(req){
    // check auth
    // validate request

    // insert requestRecord into the datbase
    // log requestRecord
    //execute transaction (background job)
        // spawn real ec2 instance
        // job clean up -> insert real product to database (UOD)
        // on fail -> delete ec2 instance, log failiure, update database

    // return response for UI
        // request = successful creation OR failure (message)
        // show optimistic "pending database" in table (actually just the requestRecord)
        
}
```


low level vender functions

