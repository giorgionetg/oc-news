<?php namespace Indikator\News\Components;

use Cms\Classes\ComponentBase;
use Cms\Classes\Page;
use Lang;
use Indikator\News\Models\Posts as NewsPost;
use Redirect;

class Posts extends ComponentBase
{
    public $posts;

    public $noPostsMessage;

    public $postPage;

    public $sortOrder;

    public function componentDetails()
    {
        return [
            'name'        => 'indikator.news::lang.settings.posts_title',
            'description' => 'indikator.news::lang.settings.posts_description'
        ];
    }

    public function defineProperties()
    {
        return [
            'pageNumber' => [
                'title'       => 'indikator.news::lang.settings.posts_pagination',
                'description' => 'indikator.news::lang.settings.posts_pagination_description',
                'type'        => 'string',
                'default'     => '{{ :page }}'
            ],
            'postsPerPage' => [
                'title'             => 'indikator.news::lang.settings.posts_per_page',
                'type'              => 'string',
                'validationPattern' => '^[0-9]+$',
                'validationMessage' => 'indikator.news::lang.settings.posts_per_page_validation',
                'default'           => '10'
            ],
            'noPostsMessage' => [
                'title'             => 'indikator.news::lang.settings.posts_no_posts',
                'description'       => 'indikator.news::lang.settings.posts_no_posts_description',
                'type'              => 'string',
                'default'           => Lang::get('indikator.news::lang.settings.posts_no_posts_found'),
                'showExternalParam' => false
            ],
            'sortOrder' => [
                'title'       => 'indikator.news::lang.settings.posts_order',
                'description' => 'indikator.news::lang.settings.posts_order_description',
                'type'        => 'dropdown',
                'default'     => 'published_at desc',
                'options'     => [
                    'title asc'         => Lang::get('indikator.news::lang.sorting.title_asc'),
                    'title desc'        => Lang::get('indikator.news::lang.sorting.title_desc'),
                    'created_at asc'    => Lang::get('indikator.news::lang.sorting.created_at_asc'),
                    'created_at desc  ' => Lang::get('indikator.news::lang.sorting.created_at_desc'),
                    'updated_at asc'    => Lang::get('indikator.news::lang.sorting.updated_at_asc'),
                    'updated_at desc'   => Lang::get('indikator.news::lang.sorting.updated_at_desc'),
                    'published_at asc'  => Lang::get('indikator.news::lang.sorting.published_at_asc'),
                    'published_at desc' => Lang::get('indikator.news::lang.sorting.published_at_desc')
                ]
            ],
            'postPage' => [
                'title'       => 'indikator.news::lang.settings.posts_post',
                'description' => 'indikator.news::lang.settings.posts_post_description',
                'default'     => 'news'
            ],
            'postFeatured' => [
                'title'       => 'indikator.news::lang.featured.title',
                'description' => 'indikator.news::lang.featured.description',
                'type'        => 'dropdown',
                'default'     => 0,
                'options'     => [
                    0 => 'indikator.news::lang.featured.otp_all',
                    1 => 'indikator.news::lang.featured.otp_featured',
                    2 => 'indikator.news::lang.featured.otp_notfeatured']
            ]
        ];
    }

    public function getPostPageOptions()
    {
        return Page::sortBy('baseFileName')->lists('baseFileName', 'baseFileName');
    }

    public function onRun()
    {
        $this->page['postPage'] = $this->property('postPage');
        $this->page['noPostsMessage'] = $this->property('noPostsMessage');

        $this->posts = $this->page['posts'] = $this->listPosts();

        if ($pageNumberParam = $this->paramName('pageNumber')) {
            $currentPage = $this->property('pageNumber');

            if ($currentPage > ($lastPage = $this->posts->lastPage()) && $currentPage > 1) {
                return Redirect::to($this->currentPageUrl([$pageNumberParam => $lastPage]));
            }
        }
    }

    protected function listPosts()
    {
        $posts = NewsPost::listFrontEnd([
            'page'    => $this->property('pageNumber'),
            'sort'    => $this->property('sortOrder'),
            'perPage' => $this->property('postsPerPage'),
            'search'  => trim(input('search')),
            'featured' => $this->property('postFeatured')
        ]);

        return $posts;
    }
}
