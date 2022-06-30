<?php

namespace App\View\Components\Layout;

use Illuminate\View\Component;

class Menu extends Component
{
    public const MENU_COMPANY = 'company';
    public const MENU_ORDER = 'order';
    public const MENU_SETTINGS = 'settings';

    public $defaultItems = [
        [
            'slug' => self::MENU_COMPANY,
            'label' => 'Компании',
            'title' => 'Компании',
            'icon' => 'fa-home',
            'class' => 'menu_company-home'
        ],
        [
            'slug' => self::MENU_ORDER,
            'label' => 'Заявки',
            'title' => 'Заявки',
            'icon' => 'fa-bars',
            'class' => 'menu_company-leads'
        ],
        [
            'slug' => self::MENU_SETTINGS,
            'label' => 'Настройки',
            'title' => 'Настройки',
            'icon' => 'fa-cog',
            'class' => 'menu_company-settings'
        ],
    ];
    public $defaultVisible = [
        self::MENU_COMPANY,
        self::MENU_ORDER,
        self::MENU_SETTINGS
    ];

    public $active;
    public $visible;

    /**
     * @var \string[]
     */
    public array $items;
    /**
     * @var mixed|null
     */
    private $company;

    /**
     * Create a new component instance.
     *
     * @param null $active
     * @param null $visible
     * @param null $items
     * @param null $company
     */
    public function __construct($active = null, $visible = null, $items = null, $company = null)
    {
        $this->items = $items ?? $this->defaultItems;
        $this->visible = $visible ?? $this->defaultVisible;
        $this->active = $active ?? $this->visible[0];
        $this->company = $company;

        $this->filterOnlyVisible();
        $this->setupLinks();
    }

    /**
     * Get the view / contents that represent the component.
     *
     * @return \Illuminate\Contracts\View\View|string
     */
    public function render()
    {
        return view('components.layout.menu');
    }

    private function setupLinks()
    {
        foreach ($this->items as &$menuItem) {
            switch ($menuItem['slug']) {
                case self::MENU_COMPANY:
                    $menuItem['link'] = route('account.companies.index');
                    break;
                case self::MENU_ORDER:
                    $menuItem['link'] = $this->company ?
                        route('account.company.proxy-leads', ['company' => $this->company->id]) :
                        '#';
                    break;
                case self::MENU_SETTINGS:
                    $menuItem['link'] = $this->company ?
                        route('account.companies.edit', ['company' => $this->company->id]) :
                        '#';
                    break;
            }
        }
    }

    private function filterOnlyVisible(): void
    {
        $this->items = collect($this->items)
            ->filter( fn($item) => in_array($item['slug'], $this->visible, true))
            ->toArray();
    }
}
