### Clever cloud deployment log


## Installation steps
### Environment
Create application, add mongodb and mysql addons

### Deployment
[github actions]

### First setup
After first deployment, SSH to application and run:

```bash
$> cd APP_DIR
$> php bin/console doctrine:schema:update --force
$> php bin/console doctrine:fixtures:load -n
$> php bin/console doctrine:mongodb:schema:create
```
