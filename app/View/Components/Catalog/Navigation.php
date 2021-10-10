<?php

namespace App\View\Components\Catalog;

use Illuminate\View\Component;

class Navigation extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Closure|\Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        $navItems = [
            [
                'title' => 'Other features',
                'route' => 'features',
            ],
            [
                'title' => 'Home',
                'route' => 'features.catalog.search',
            ],
            [
                'title' => 'Authors',
                'route' => 'features.catalog.authors',
            ],
            [
                'title' => 'Series',
                'route' => 'features.catalog.series',
            ],
        ];

        return view('components.catalog.navigation', compact('navItems'));
    }
}
