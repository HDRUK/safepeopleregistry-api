## SPeeDI-AS API Tiltfile
##
## Loki Sinclair <loki.sinclair@hdruk.ac.uk>
##

# Load in any locally set config
cfg = read_json('tiltconf.json')

include(cfg.get('speediWebRoot') + '/Tiltfile')
include(cfg.get('clamAVRoot') + '/Tiltfile')

docker_build(
    ref='hdruk/' + cfg.get('name'),
    context='.',
    build_args={},
    live_update=[
        sync('.', '/var/www'),
        run('composer install', trigger='./composer.lock'),
        run('php artisan route:clear'),
        run('php artisan cache:clear'),
        run('php artisan config:clear', trigger='./.env'),
        run('php artisan l5-swagger:generate'),
    ]
)

k8s_yaml('chart/' + cfg.get('name') + '/deployment.yaml')
k8s_yaml('chart/' + cfg.get('name') + '/service.yaml')
k8s_resource(
   cfg.get('name'),
   port_forwards=8100,
   labels=["Service"]
)