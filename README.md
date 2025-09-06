
### Run application on local env
```bash

   php artisan migrate
   
   php artisan schedule:work 
   
   php artisan queue:work
   
   php artisan app:generate-random-orders # for create random orders
   
   php artisan app:app:matched-random-orders # for running matching engine
   
   php artisan serve #for running server
```
#### if any orders didnt match rerun the Application
```bash
  php artisan rerun-app
```
