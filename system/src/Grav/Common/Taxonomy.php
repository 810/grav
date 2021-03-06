<?php
namespace Grav\Common;

use \Grav\Common\Page;

/**
 * The Taxonomy object is a singleton that holds a reference to a 'taxonomy map'. This map is
 * constructed as a multidimensional array.
 *
 * uses the taxonomy defined in the site.yaml file and is built when the page objects are recursed.
 * Basically every time a page is found that has taxonomy references, an entry to the page is stored in
 * the taxonomy map.  The map has the following format:
 *
 * [taxonomy_type][taxonomy_value][page_path]
 *
 * For example:
 *
 * [category][blog][path/to/item1]
 * [tag][grav][path/to/item1]
 * [tag][grav][path/to/item2]
 * [tag][dog][path/to/item3]
 *
 * @author RocketTheme
 * @license MIT
 */
class Taxonomy
{
    protected $taxonomy_map;

    /**
     * Constructor that resets the map
     */
    public function __construct()
    {
        $this->taxonomy_map = array();
    }

    /**
     * Takes an individual page and processes the taxonomies configured in its header. It
     * then adds those taxonomies to the map
     *
     * @param Page\Page $page the page to process
     * @param array $page_taxonomy
     */
    public function addTaxonomy(Page\Page $page, $page_taxonomy = null)
    {
        if (!$page_taxonomy) {
            $page_taxonomy = $page->taxonomy();
        }

        $config = Registry::get('Config');
        if ($config->get('site.taxonomies') && count($page_taxonomy) > 0) {
            foreach ((array) $config->get('site.taxonomies') as $taxonomy) {
                if (isset($page_taxonomy[$taxonomy])) {
                    foreach ((array) $page_taxonomy[$taxonomy] as $item) {
                        // TODO: move to pages class?
                        $this->taxonomy_map[$taxonomy][(string) $item][$page->path()] = array('slug' => $page->slug());
                    }
                }
            }
        }
    }

    /**
     * Returns a new Page object with the sub-pages containing all the values set for a
     * particular taxonomy.
     *
     * @param  array $taxonomies taxonomies to search, eg ['tag'=>['animal','cat']]
     * @return Page\Page             page object with sub-pages set to contain matches found in the taxonomy map
     */
    public function findTaxonomy($taxonomies)
    {
        $results = array();

        foreach ((array)$taxonomies as $taxonomy => $items) {
            foreach ((array) $items as $item) {
                if (isset($this->taxonomy_map[$taxonomy][$item])) {
                    $results = array_merge($results, $this->taxonomy_map[$taxonomy][$item]);
                }
            }
        }

        return new Page\Collection($results, ['taxonomies' => $taxonomies]);
    }

    /**
     * Gets and Sets the taxonomy map
     *
     * @param  array $var the taxonomy map
     * @return array      the taxonomy map
     */
    public function taxonomy($var = null)
    {
        if ($var) {
            $this->taxonomy_map = $var;
        }
        return $this->taxonomy_map;
    }
}
