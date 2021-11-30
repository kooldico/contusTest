<?php

/**
 * Category Repository
 *
 * To manage the functionalities related to the Categories module from Categories Controller
 *
 * @name CategoriesRepository
 * @vendor Contus
 * @package Categories
 * @version 1.0
 * @author Contus<developers@contus.in>
 * @copyright Copyright (C) 2016 Contus. All rights reserved.
 * @license GNU General Public License http://www.gnu.org/copyleft/gpl.html
 */
namespace Contus\Video\Repositories;


use Contus\Video\Models\Category;
use Contus\Base\Repository as BaseRepository;
use Contus\Base\Helpers\StringLiterals;
use Contus\Video\Traits\CategoryTrait as CategoryTrait;
use Contus\Video\Traits\VideoTrait as VideoTrait;
class CategoryRepository extends BaseRepository{
    use CategoryTrait, VideoTrait;
    /**
     * Class property to hold the key which hold the user object
     *
     * @var object
     */
    protected $_category;
    /**
     * Class property to hold the key which hold the group name requested
     *
     * @var string
     */
    protected $requestedCategories = 'q';
    /**
     * Construct method
     *
     * @vendor Contus
     *
     * @package Video
     * @param Contus\Video\Models\Categories $categories
     */
    public function __construct(Category $category) {
        parent::__construct ();
        $this->_category = $category;
        $this->setRules ( [ StringLiterals::TITLE => 'required' ] );
    }   
    
    /**
     * Repository function to get the parentcategory list
     *
     * @param integer $id
     * @return variable
     */
    public function getParentCategory($id) {
        $categoryData = $this->_category->find ( $id );
        $categoryData = explode ( '/', $categoryData->level );
        $parentCategoryTitle = [ ];
        $parentcategoryData = [ ];
        foreach ( $categoryData as $value ) {
            // code...
            if ($value != 0) {
                $parentcategoryTitleData = $this->_category->select ( 'id', StringLiterals::TITLE )->find ( $value );
                $parentCategoryTitle [$parentcategoryTitleData->id] = $parentcategoryTitleData->title;
                $parentcategoryData [] = $this->_category->find ( $value );
            }
        }
        return array ('parentcategoryTitle' => $parentCategoryTitle,'parentcategoryData' => $parentcategoryData );
    }
    /**
     * Function to get all categories.
     *
     * @return array All categories.
     */
    public function getAllCategories($slug = '') {
        $subcatvalue = [ ];
        if ($slug) {
            $categoryinfo = $this->_category->where ( $this->getKeySlugorId (), $slug )->where ( 'is_active', 1 )->where ( 'parent_id', 0 )->with ( 'child_category.child_category' )->get ();
        } else {
            $categoryinfo = $this->_category->where ( 'parent_id', 0 )->where ( 'is_active', 1 )->with ( 'child_category.child_category' )->get ()->toArray ();
        }
        if (count ( $categoryinfo ) > 0) {
            foreach ( $categoryinfo as $value ) {
                if (count ( $value ['child_category'] ) > 0) {
                    $subcatvalue = $subcatvalue + $this->getChildCategoryEach ( $value );
                }
            }
        }
        return $subcatvalue;
    }
}