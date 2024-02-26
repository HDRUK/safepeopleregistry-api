#!/usr/bin/env bash

POD=$(kubectl get pod -l app=speedi-as-api -o jsonpath="{.items[0].metadata.name}")

kubectl exec -it $POD -- composer run phpstan
kubectl exec -it $POD -- composer run pest