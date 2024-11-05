## Setting up the database

Create an .env based on .env.example and put in your database connection details:

```
DB_CONNECTION=mysql
DB_HOST=[MySql-hostname] //This could be the pod name in kubernetes less the -[n]
DB_PORT=3306
DB_DATABASE=speedi-as-api
DB_USERNAME=root
DB_PASSWORD=
```

Forward port 3306 so you are able to connect the the MySql instance with a client:

```kubectl port-forward [MySql-pod-name] 3306:3306```

Create an empty table using you client called speedi-as-api

Run database migrations:

```kubectl exec -it service/speedi-as-api -- php artisan migrate

kubectl exec -it service/speedi-as-api -- php artisan db:seed```



