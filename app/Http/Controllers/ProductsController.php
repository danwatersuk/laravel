<?php

namespace App\Http\Controllers;

use App\Models\StoreProduct;
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
    protected $products;

    public function __construct()
    {
        /* As the system manages multiple stores a storeBuilder instance would
        normally be passed here with a store object. The id of the example 
        store is being set here for the purpose of the test */
        $this->storeId = 3;
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

    public function addPaginate(Request $request) {
        //$this->paginateCount = $request->input('paginateCount');
        //$this->page = $request->input('page');
        $this->products->paginate($this->paginateCount);
    }

    public function products(Request $request) {
        $this->products = StoreProduct
            ::with('sections', 'artist');
        $this->addSortLogic($request);
        $this->addPaginate($request);
        return json_encode($this->products->get());
    }

    public function section($section, Request $request) {
        $this->products = StoreProduct
            ::with('sections', 'artist')
            ->whereHas('sections',
            function($query) use ($section) {
                $query->whereLike('description', $section)
                    ->orWhere('id', $section);
            });
        $this->addSortLogic($request);
        $this->addPaginate($request);
    }
}
