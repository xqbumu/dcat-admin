<?php

namespace Dcat\Admin\Grid\Concerns;

use Dcat\Admin\Grid\Column;
use Dcat\Admin\Grid\Displayers\AbstractDisplayer;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;

trait Displayers
{
    /**
     * Display using display abstract.
     *
     * @param string $abstract
     * @param array  $arguments
     *
     * @return Column
     */
    public function displayUsing($abstract, $arguments = [])
    {
        $grid = $this->grid;

        $column = $this;

        return $this->display(function ($value) use ($grid, $column, $abstract, $arguments) {
            /** @var AbstractDisplayer $displayer */
            $displayer = new $abstract($value, $grid, $column, $this);

            return $displayer->display(...$arguments);
        });
    }

    /**
     * Display column using array value map.
     *
     * @param array $values
     * @param null  $default
     *
     * @return $this
     */
    public function using(array $values, $default = null)
    {
        return $this->display(function ($value) use ($values, $default) {
            if (is_null($value)) {
                return $default;
            }

            return Arr::get($values, $value, $default);
        });
    }

    /**
     * @param string $color
     * @return $this
     */
    public function bold($color = 'text-80')
    {
        return $this->display(function ($value) use ($color) {
            if (!$value) {
                return $value;
            }

            return "<b class='$color'>$value</b>";
        });
    }

    /**
     * Display column with "long2ip".
     *
     * @param null $default
     * @return $this
     */
    public function long2ip($default = null)
    {
        return $this->display(function ($value) use ($default) {
            if (!$value) {
                return $default;
            }

            return long2ip($value);
        });
    }

    /**
     * Render this column with the given view.
     *
     * @param string $view
     *
     * @return $this
     */
    public function view($view)
    {
        $name = $this->name;

        return $this->display(function ($value) use ($view, $name) {
            $model = $this;

            return view($view, compact('model', 'value', 'name'))->render();
        });
    }

    /**
     * @param string $val
     * @return $this
     */
    public function prepend($val)
    {
        return $this->display(function ($v) use (&$val) {
            if (is_array($v)) {
                array_unshift($v, $val);

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->prepend($val);
            }
            return $val . $v;
        });
    }

    /**
     * @param string $val
     * @return $this
     */
    public function append($val)
    {
        return $this->display(function ($v) use (&$val) {
            if (is_array($v)) {
                array_push($v, $val);

                return $v;
            } elseif ($v instanceof Collection) {
                return $v->push($val);
            }
            return $v . $val;
        });
    }

    /**
     * Split a string by string
     *
     * @param string $d
     * @return $this
     */
    public function explode(string $d = ',')
    {
        return $this->display(function ($v) use ($d) {
            if (is_array($v) || $v instanceof Arrayable) return $v;

            return $v ? explode($d, $v) : [];
        });
    }
}
