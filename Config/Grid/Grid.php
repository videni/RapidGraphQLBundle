<?php

namespace Videni\Bundle\RestBundle\Config\Grid;

use Webmozart\Assert\Assert;

/**
 * Represents the configuration of  paginator
 */
class Grid
{
    private $maxResults;
    private $disableSorting;
    private $class;

    /** @var Filter[] */
    protected $filters = [];
    protected $fields =  [];
    protected $actionGroups = [];

    /**
     * @return mixed
     */
    public function getMaxResults()
    {
        return $this->maxResults;
    }

    /**
     * @param mixed $maxResults
     *
     * @return self
     */
    public function setMaxResults($maxResults)
    {
        $this->maxResults = $maxResults;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getDisableSorting()
    {
        return $this->disableSorting;
    }

    /**
     * @param mixed $disableSorting
     *
     * @return self
     */
    public function setDisableSorting($disableSorting)
    {
        $this->disableSorting = $disableSorting;

        return $this;
    }

    /**
     * Checks whether the configuration of at least one filter exists.
     *
     * @return bool
     */
    public function hasFilters()
    {
        return !empty($this->filters);
    }

    /**
     * Gets the configuration for all filters.
     *
     * @return Filter[] [filter name => config, ...]
     */
    public function getFilters()
    {
        return $this->filters;
    }

    /**
     * Checks whether the configuration of the filter exists.
     *
     * @param string $filterName
     *
     * @return bool
     */
    public function hasFilter($filterName)
    {
        return isset($this->filters[$filterName]);
    }

    /**
     * Gets the configuration of the filter.
     *
     * @param string $filterName
     *
     * @return Filter|null
     */
    public function getFilter($filterName)
    {
        if (!isset($this->filters[$filterName])) {
            return null;
        }

        return $this->filters[$filterName];
    }

    /**
     * Adds the configuration of the filter.
     *
     * @param string                 $filterName
     * @param Filter|null $filter
     *
     * @return Filter
     */
    public function addFilter($filterName, $filter = null)
    {
        if (null === $filter) {
            $filter = new Filter();
        }

        $this->filters[$filterName] = $filter;

        return $filter;
    }

    /**
     * Removes the configuration of the filter.
     *
     * @param string $filterName
     */
    public function removeFilter($filterName)
    {
        unset($this->filters[$filterName]);
    }

    /**
     * @return mixed
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param mixed $class
     *
     * @return self
     */
    public function setClass($class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return array
     */
    public function getActionGroups(): array
    {
        return $this->actionGroups;
    }

    /**
     * @return array
     */
    public function getEnabledActionGroups(): array
    {
        return $this->getEnabledItems($this->getActionGroups());
    }

    /**
     * @param ActionGroup $actionGroup
     *
     * @throws \InvalidArgumentException
     */
    public function addActionGroup(ActionGroup $actionGroup): void
    {
        $name = $actionGroup->getName();

        Assert::false($this->hasActionGroup($name), sprintf('ActionGroup "%s" already exists.', $name));

        $this->actionGroups[$name] = $actionGroup;
    }

    /**
     * @param string $name
     */
    public function removeActionGroup(string $name): void
    {
        if ($this->hasActionGroup($name)) {
            unset($this->actionGroups[$name]);
        }
    }

    /**
     * @param string $name
     *
     * @return ActionGroup
     */
    public function getActionGroup(string $name): ActionGroup
    {
        Assert::true($this->hasActionGroup($name), sprintf('ActionGroup "%s" does not exist.', $name));

        return $this->actionGroups[$name];
    }

    /**
     * @param ActionGroup $actionGroup
     */
    public function setActionGroup(ActionGroup $actionGroup): void
    {
        $name = $actionGroup->getName();

        $this->actionGroups[$name] = $actionGroup;
    }

    /**
     * @param string $groupName
     *
     * @return Action[]
     */
    public function getActions(string $groupName): array
    {
        return $this->getActionGroup($groupName)->getActions();
    }

    /**
     * @return array
     */
    public function getEnabledActions($groupName): array
    {
        return $this->getEnabledItems($this->getActions($groupName));
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasActionGroup(string $name): bool
    {
        return array_key_exists($name, $this->actionGroups);
    }

    /**
     * @return array
     */
    public function getFields(): array
    {
        return $this->fields;
    }

    /**
     * @return array
     */
    public function getEnabledFields(): array
    {
        return $this->getEnabledItems($this->getFields());
    }

    public function getSortings()
    {
       $fields = $this->getEnabledFields();

       return array_filter($fields, function($field) {
            return true === $field->getSorting();
        });
    }

    /**
     * @param Field $field
     *
     * @throws \InvalidArgumentException
     */
    public function addField(Field $field): void
    {
        $name = $field->getName();

        Assert::false($this->hasField($name), sprintf('Field "%s" already exists.', $name));

        $this->fields[$name] = $field;
    }

    /**
     * @param string $name
     */
    public function removeField(string $name): void
    {
        if ($this->hasField($name)) {
            unset($this->fields[$name]);
        }
    }

    /**
     * @param string $name
     *
     * @return Field
     *
     * @throws \InvalidArgumentException
     */
    public function getField(string $name): Field
    {
        Assert::true($this->hasField($name), sprintf('Field "%s" does not exist.', $name));

        return $this->fields[$name];
    }

    /**
     * @param Field $field
     */
    public function setField(Field $field): void
    {
        $name = $field->getName();

        $this->fields[$name] = $field;
    }

    /**
     * @param string $name
     *
     * @return bool
     */
    public function hasField(string $name): bool
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * @param array $items
     *
     * @return array
     */
    private function getEnabledItems(array $items): array
    {
        $filteredItems = [];
        foreach ($items as $item) {
            if ($item->isEnabled()) {
                $filteredItems[] = $item;
            }
        }

        return $filteredItems;
    }
}
