<?php

namespace Videni\Bundle\RestBundle\Processor;

use Oro\Component\ChainProcessor\ParameterBag;
use Oro\Component\ChainProcessor\ParameterBagInterface;
use Oro\Component\ChainProcessor\Context as BaseContext;
use Symfony\Component\HttpFoundation\Request;
use Videni\Bundle\RestBundle\Filter\FilterCollection;
use Videni\Bundle\RestBundle\Filter\FilterValue\FilterValueAccessorInterface;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfig;
use Videni\Bundle\RestBundle\Config\Resource\ResourceConfigProvider;

class Context extends BaseContext implements ContextInterface
{
    /** FQCN of an entity */
    const CLASS_NAME = 'class';

    const VERSION = 'version';

     /** metadata of an entity */
    const RESOURCE_CONFIG = 'resource_config';

    const FORMAT = 'format';

    const CRITERIA = 'criteria';

    /**
     * a value indicates whether errors should just stop processing
     * or an exception should be thrown is any error occurred
     */
    const SOFT_ERRORS_HANDLING = 'softErrorsHandling';

    /** a query is used to get result data */
    const QUERY = 'query';

    /** the response status code */
    const RESPONSE_STATUS_CODE = 'responseStatusCode';

    const OPERATION_NAME = 'operationName';

    /** @var Error[] */
    private $errors;

    /** @var ParameterBagInterface */
    private $requestHeaders;

    /** @var ParameterBagInterface */
    private $responseHeaders;

    private $requestData;

    private $resourceConfigProvider;

    /** @var FilterCollection */
    private $filters;

    private $filterValues;

    /**
     * @param ResourceConfigProvider $resourceConfigProvider
     */
    public function __construct(
        ResourceConfigProvider $resourceConfigProvider
    ) {
        $this->resourceConfigProvider = $resourceConfigProvider;
    }

    /**
     * Gets API version
     *
     * @return string
     */
    public function getVersion()
    {
        return $this->get(self::VERSION);
    }

    /**
     * Sets API version
     *
     * @param string $version
     */
    public function setVersion($version)
    {
        $this->set(self::VERSION, $version);
    }

    public function setOperationName($operationName)
    {
        $this->set(self::OPERATION_NAME, $operationName);
    }

    public function getOperationName()
    {
        return $this->get(self::OPERATION_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->get(self::CLASS_NAME);
    }

    /**
     * {@inheritdoc}
     */
    public function setClassName($className)
    {
        $this->set(self::CLASS_NAME, $className);
    }

    public function setFormat($format)
    {
        $this->set(self::FORMAT, $format);
    }

    public function getFormat()
    {
        return $this->get(self::FORMAT);
    }

    /**
     * {@inheritdoc}
     */
    public function getRequestHeaders()
    {
        if (null === $this->requestHeaders) {
            $this->requestHeaders = new CaseInsensitiveParameterBag();
        }

        return $this->requestHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function setRequestHeaders(ParameterBagInterface $parameterBag)
    {
        $this->requestHeaders = $parameterBag;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseHeaders()
    {
        if (null === $this->responseHeaders) {
            $this->responseHeaders = new ParameterBag();
        }

        return $this->responseHeaders;
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseHeaders(ParameterBagInterface $parameterBag)
    {
        $this->responseHeaders = $parameterBag;
    }

    /**
     * {@inheritdoc}
     */
    public function getResponseStatusCode()
    {
        return $this->get(self::RESPONSE_STATUS_CODE);
    }

    /**
     * {@inheritdoc}
     */
    public function setResponseStatusCode($statusCode)
    {
        $this->set(self::RESPONSE_STATUS_CODE, $statusCode);
    }

    /**
     * {@inheritdoc}
     */
    public function isSuccessResponse()
    {
        $statusCode = $this->getResponseStatusCode();

        return $statusCode>= 200 && $statusCode < 300;
    }

    /**
     * {@inheritdoc}
     */
    public function hasQuery()
    {
        return $this->has(self::QUERY);
    }

    /**
     * {@inheritdoc}
     */
    public function getQuery()
    {
        return $this->get(self::QUERY);
    }

    /**
     * {@inheritdoc}
     */
    public function setQuery($query)
    {
        if ($query) {
            $this->set(self::QUERY, $query);
        } else {
            $this->remove(self::QUERY);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function hasErrors()
    {
        return !empty($this->errors);
    }

    /**
     * {@inheritdoc}
     */
    public function getErrors()
    {
        return null !== $this->errors
            ? $this->errors
            : [];
    }

    /**
     * {@inheritdoc}
     */
    public function addError(Error $error)
    {
        if (null === $this->errors) {
            $this->errors = [];
        }
        $this->errors[] = $error;
    }

    /**
     * {@inheritdoc}
     */
    public function resetErrors()
    {
        $this->errors = null;
    }

    /**
     * Gets a value indicates whether errors should just stop processing
     * or an exception should be thrown is any error occurred.
     *
     * @return bool
     */
    public function isSoftErrorsHandling()
    {
        return (bool)$this->get(self::SOFT_ERRORS_HANDLING);
    }

    /**
     * Sets a value indicates whether errors should just stop processing
     * or an exception should be thrown is any error occurred.
     *
     * @param bool $softErrorsHandling
     */
    public function setSoftErrorsHandling($softErrorsHandling)
    {
        if ($softErrorsHandling) {
            $this->set(self::SOFT_ERRORS_HANDLING, true);
        } else {
            $this->remove(self::SOFT_ERRORS_HANDLING);
        }
    }

    /**
     * Returns request.
     *
     * @return Request
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * Sets request to the context.
     *
     * @param Request request
     */
    public function setRequest(Request $request)
    {
        $this->request = $request;
    }

      /**
     * {@inheritdoc}
     */
    public function hasResourceConfig()
    {
        return $this->has(self::RESOURCE_CONFIG);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceConfig()
    {
        if (!$this->has(self::RESOURCE_CONFIG)) {
            $this->loadResourceConfig();
        }

        return $this->get(self::RESOURCE_CONFIG);
    }

    /**
     * {@inheritdoc}
     */
    public function setResourceConfig(?ResourceConfig $resourceConfig)
    {
        if ($resourceConfig) {
            $this->set(self::RESOURCE_CONFIG, $resourceConfig);
        } else {
            $this->remove(self::RESOURCE_CONFIG);
        }
    }

    /**
     * Loads an entity metadata.
     */
    public function loadResourceConfig()
    {
        $entityClass = $this->getClassName();
        if (empty($entityClass)) {
            return;
        }

        $resourceConfig = $this->resourceConfigProvider->get($entityClass);

        $this->set(self::RESOURCE_CONFIG, $resourceConfig);
    }

        /**
     * {@inheritdoc}
     */
    public function getFilters()
    {
        if (null === $this->filters) {
            $this->filters = new FilterCollection();
        }

        return $this->filters;
    }

    /**
     * {@inheritdoc}
     */
    public function getFilterValues()
    {
        if (null === $this->filterValues) {
            $this->filterValues = new NullFilterValueAccessor();
        }

        return $this->filterValues;
    }

    /**
     * {@inheritdoc}
     */
    public function setFilterValues(FilterValueAccessorInterface $accessor)
    {
        $this->filterValues = $accessor;
    }

    /**
     * {@inheritdoc}
     */
    public function getCriteria()
    {
        return $this->get(self::CRITERIA);
    }

    /**
     * {@inheritdoc}
     */
    public function setCriteria($criteria)
    {
        $this->set(self::CRITERIA, $criteria);
    }
}
