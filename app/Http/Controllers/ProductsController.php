<?php

namespace App\Http\Controllers;

use App\Models\StoreProduct;
use App\Utility\Geocode;
use Illuminate\Http\Request;

class ProductsController extends Controller
{
    public $storeId;
    public $page = 1;
    public $paginateCount = 8;
    public $sortMap = [
        "az" => ["name","asc"],
        "za" => ["name","desc"],
        "low" => ["price","asc"],
        "high" => ["price","desc"],
        "old" => ["release_date","asc"],
        "new" => ["release_date", "desc"]
    ];
    protected $dateFormat = 'Y-m-d H:i:s';
    protected $nowDate;
    protected $products;

    public function __construct()
    {
        /* As the system manages multiple stores a storeBuilder instance would
        normally be passed here with a store object. The id of the example 
        store is being set here for the purpose of the test */
        $this->storeId = 3;
        $this->nowDate = new \Carbon\Carbon();
    }
    
    public function addFilterLogic() {
        $this->products
            ->whereRaw("NOT FIND_IN_SET(".(new Geocode())['country'].", 'disabled_countries')")
            ->where('launch_date', '<=', $this->nowDate->format($this->dateFormat))
            ->where('remove_date', '>', $this->nowDate->format($this->dateFormat));
    }

    public function addSortLogic(Request $request) {
        $sort = $request->input('sort');
        $this->products->when(!is_null($sort), function($query, $sort) {
            $query->orderBy($this->sortMap[$sort][0], $this->sortMap[$sort][1]);
        }, function($query) {
            if ((isset($section) && ($section == "%" || $section == "all"))) {
                //$order = "ORDER BY sp.position ASC, release_date DESC";
            } else {
                $query->orderBy('position', 'ASC');
                $query->orderBy('release_date', 'DESC');
                //$order = "ORDER BY store_products_section.position ASC, release_date DESC";
            }
        });
    }

    public function addSelect() {
        $this->products->select(
            'store_products.id AS id',
            'artists.name AS artist',
            'store_products.title AS title',
            'store_products.description AS description'
            //'price',
            //'format',
            //'release_date'
        );
    }

    public function addPaginate(Request $request) {
        //$this->paginateCount = $request->input('paginateCount');
        //$this->page = $request->input('page');
        $this->products->paginate($this->paginateCount);
    }

    public function products(Request $request) {
        $this->products = StoreProduct
            ::with(['sections', 'artists']);
        $this->addSelect();
        $this->addSortLogic($request);
        $this->addPaginate($request);
        dd($this->products->toSql());
        return json_encode($this->products->get());
    }

    public function section($section, Request $request) {
        $this->products = StoreProduct
            ::with(['sections' =>
            function($query) use ($section) {
                $query->whereLike('sections.description', $section)
                    ->orWhere('sections.id', $section);
            }, 'artist']);
        $this->addSelect();
        //$this->addSortLogic($request);
        //$this->addPaginate($request);
        dd($this->products->toSql());
        return json_encode($this->products->get());
    }
}
 