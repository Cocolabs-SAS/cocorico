# MongoDB

Listing prices and availabilities are stored in MongoDB through ListingAvailability and ListingAvailabilityTime
documents. 

* To create schema
    
    `php app/console doctrine:mongodb:schema:create`

* To update schema
    
    `php app/console doctrine:mongodb:schema:update`
    
* To execute command

    * From Robomongo
    
        `db.getCollection('listing_availabilities').find({"lId": 1926723337, "d": { "$gte" : ISODate("2017-03-20T00:00:00Z"), "$lt" : ISODate("2017-03-21T00:00:00Z") }})`
    
    * From shell
    
        `use cocorico; show collections;db.run.Command(Your command)`


**Note:** For PHP >= 7 use PHP mongodb extension
