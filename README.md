Simple REST Bundle
===============
A rapid RESTfull API bundle, it can help you build your API extremely faster.

## Features

* CRUD
* Swagger Doc
* API version(JMS serializer)
* Map request to entity via Symfony Form
* HAL
* Custom paginator(filter, sorting for pagination)

## Ingredients

* willdurand/hateoas-bundle
* lexik/jwt-authentication-bundle
* oro/chain-processor


## Usage

1.  Specify bundles for API resources descovery

```
use Oro\Component\Config\CumulativeResourceManager;
use Symfony\Component\HttpKernel\Kernel as BaseKernel;

class Kernel extends BaseKernel
{
     /**
     * {@inheritdoc}
     */
    protected function initializeBundles()
    {
        parent::initializeBundles();

        // pass bundles to CumulativeResourceManager
        $bundles = array();
        foreach ($this->bundles as $name => $bundle) {
            $bundles[$name] = get_class($bundle);
        }

        CumulativeResourceManager::getInstance()
            ->setBundles($bundles)
            ->setAppRootDir($this->getProjectDir().'/src')
        ;

        return $bundles;
    }
}
```


## Todo

1. Resolve resource API configurations from project config directory, such as config/rest

2. Compire resource configuration when resource API definition file changed.


## Demo

Check [SimpleRestDemo](https://github.com/videni/SimpleRestDemo)
