## About SPEEDI-AS-API
Information regarding the running and usage of the SPEEDI-AS-API Software

## Rules Engine
We use GoRules.io for our BRMS/DMN system. In order to make use of this yourself, follow these steps:

    1. Deploy the postgres helm chart: 
    ```
    helm install speedi-pgsql --set global.postgresql.auth.postgresPassword="Challenge12Havoc?" \
        --set global.postgresql.auth.username="speedi-as" \
        --set global.postgresql.auth.password="Flood15?Voice" \
        --set global.postgresql.auth.database="speedi-rules" \
        oci://registry-1.docker.io/bitnamicharts/postgresql
    ```

    2. Next is to deploy gorules-brms:
        - Download `values.yaml` from https://artifacthub.io/packages/helm/gorules/gorules-brms?modal=values
        - Feel free to rename `values.yaml` to `values-dev.yaml`
        - Replace variables, such as `appUrl` and `licenseKey` with your values
        - Complete the postgres connection details
    
        - Deploy the chart
            ```
            helm install speedi-rules oci://registry-1.docker.io/gorulescharts/gorules-brms -f values[-dev].yaml
            ```
        - Test your installation
            - `kubectl port-forward services/speedi-rules 4200:80` and the BRMS will be locally available on http://localhost:4200/