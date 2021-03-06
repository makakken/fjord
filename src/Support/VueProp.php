<?php

namespace Fjord\Support;

use Illuminate\Support\Collection;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Contracts\Support\Arrayable;
use Fjord\Vue\Contracts\AuthorizableContract;

abstract class VueProp implements Arrayable, Jsonable
{
    /**
     * Return array that should be passed to Vue.
     *
     * @return array
     */
    abstract protected function render(): array;

    /**
     * Get array.
     *
     * @return array
     */
    public function toArray()
    {
        return $this->getRendered()->toArray();
    }

    /**
     * To json.
     *
     * @param integer $options
     * @return void
     */
    public function toJson($options = 0)
    {
        return $this->getRendered()->toJson($options);
    }

    /**
     * Get rendered.
     *
     * @return \Illuminate\Support\Collection
     */
    protected function getRendered(): Collection
    {
        // When all props are passed to the Vue application, they are converted 
        // to an array using Laravel's collection method toArray. To ensure that 
        // all nested objects are converted, the array is converted to a 
        // collection instance, which then calls the toArray method on all items.
        $rendered = collect($this->render());

        return $this->filterAuthorized($rendered);
    }

    /**
     * Filter rendered collection authorized items.
     *
     * @param Collection $rendered
     * @return Collection
     */
    protected function filterAuthorized(Collection $rendered)
    {
        foreach ($rendered as $key => $item) {
            if (!$item instanceof AuthorizableContract) {
                continue;
            }

            if (!$item->isAuthorized()) {
                unset($rendered[$key]);
            }
        }

        return $rendered;
    }
}
