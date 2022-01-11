<?php

/*
	* Forum Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Forum Class
	* @since 4.1.5
*/
declare(strict_types=1);
class Forum
{

    private $_db,
        $_user;

    public static $page_title = 'Home Page';
    /*
	* Check Forum
	* @since 4.1.5
*/
    public function __construct()
    {
        $this->_db = Database::getInstance();
        $this->_user = new User();
    }

/*
	* Get Categories
	* @Since 4.5.1
	* @Param ()
*/
    public function getCategories():string
    {
        $contentReturn = $topics_list  ='';
        $cat_div = new Template("cat_div.tpl");
        $topic_div_list = new Template("topic_div_list.tpl");

        $sql_categories = sprintf("SELECT categories.cat_id, categories.cat_name, categories.cat_description,  COUNT(topics.topic_id) AS topics FROM categories LEFT JOIN topics ON topics.topic_id = categories.cat_id GROUP BY categories.cat_name, categories.cat_description, categories.cat_id;");
        $getCategories = $this->_db->query($sql_categories);
        if ($getCategories->count()) {
            foreach ($getCategories->results() as $categories) {
                if ($this->_user->hasPermission($categories->permission) || ($categories->permission = 'all'  && $this->_user->hasPermission($categories->permission))) {
                    $sql_topics = sprintf("SELECT topic_id,topic_subject,topic_date,topic_cat FROM topics WHERE topic_cat = '%d'ORDER BY topic_date DESC LIMIT 1;",$categories->cat_id);
                    $getTopics = $this->_db->query($sql_topics);
                    if ($getTopics->count()) {
                        foreach ($getTopics->results() as $topics) {
                            if ($this->_user->hasPermission($topics->permission) || ($categories->permission = 'all'  && $this->_user->hasPermission($topics->permission))) {
                                $topic_div_list->setArray(array(
                                    'topic_id'  =>  $topics->topic_id,
                                    'topic_cat' =>  $topics->topic_cat,
                                    'title'     =>  $topics->topic_subject,
                                    'date'      =>  $topics->topic_date,
                                    'postedby'  =>  $topics->topic_by
                                ));
                                $topics_list .= $topic_div_list->show();
                            }
                        }
                    } else {
                        $topic_div_list->setArray(array(
                            'topic_id'  =>  '',
                            'topic_cat' =>  '',
                            'title'     =>  'No topic could be displayed.',
                            'date'      =>  '',
                            'postedby'  =>  ''
                        ));
                        $topics_list .= $topic_div_list->show();
                    }
                    $cat_div->setArray(array(
                        'cat_id'        =>  $categories->cat_id,
                        'title'         =>  $categories->cat_name,
                        'description'   =>  $categories->cat_description,
                        'topics'        =>  $topics_list
                    ));
                    $contentReturn .= $cat_div->show();
                }
            }
        }
        self::$page_title = 'Home';
        return $contentReturn;
    }

/*
	* Get Topics in Categories
	* @Since 4.5.1
	* @Param ()
*/
    public function getTopicsInCategory():string
    {
        $contentReturn = $topics_list =$returnTitle= '';
        $cat_top = new Template("cat_top.tpl");
        $topic_div_list = new Template("topic_div_list.tpl");

        $sql_categories = sprintf("SELECT  cat_id, cat_name, cat_description FROM categories WHERE cat_id = '%b';",Input::get('cat_id'));
        $getCategories = $this->_db->query($sql_categories);

        $categories = $getCategories->results()->first();
        if ($this->_user->hasPermission($categories->permission) || ($categories->permission = 'all'  && $this->_user->hasPermission($categories->permission))) {
            if ($getCategories->count()) {
                $sql_topics = sprintf("SELECT topic_id,  topic_subject, topic_date,  topic_cat FROM  topics WHERE  topic_cat = '%b';",Input::get('topic_id') );
                $getTopics = $this->_db->query($sql_topics);
                if ($getTopics->count()) {
                    foreach ($getTopics->results() as $topics) {
                        if ($this->_user->hasPermission($topics->permission) || ($categories->permission = 'all'  && $this->_user->hasPermission($topics->permission))) {
                            $topic_div_list->setArray(array(
                                'topic_id'  =>  $topics->topic_id,
                                'topic_cat' =>  $topics->topic_cat,
                                'title'     =>  $topics->topic_subject,
                                'date'      =>  $topics->topic_date,
                                'postedby'  =>  $topics->topic_by
                            ));
                            $topics_list .= $topic_div_list->show();
                        }
                    }
                    $cat_top->setArray(array(
                        'cat_id'        =>  $categories->cat_id,
                        'name'          =>  $categories->cat_name,
                        'description'   =>  $categories->cat_description,
                        'topics'        =>  $topic_div_list->show()
                    ));
                    $contentReturn .= $cat_top->show();
                    $returnTitle = $categories->cat_name;
                } else {
                    $topic_div_list->setArray(array(
                        'topic_id'  =>  '',
                        'topic_cat' =>  '',
                        'title'     =>  'No topic could be displayed.',
                        'date'      =>  '',
                        'postedby'  =>  ''
                    ));
                    $cat_top->setArray(array(
                        'cat_id'        =>  '',
                        'name'         =>  'You do Not have permission to access this Topic',
                        'description'   =>  'You do Not have permission to access this Topic',
                        'topics'        =>  $topic_div_list->show()
                    ));
                    $contentReturn .= $cat_top->show();
                    $returnTitle = 'No topic could be displayed.';
                }
            }
        } else {
            $topic_div_list->setArray(array(
                'topic_id'  =>  '',
                'topic_cat' =>  '',
                'title'     => 'You do Not have permission to access this Topic',
                'date'      =>  '',
                'postedby'  =>  ''
            ));
            $cat_top->setArray(array(
                'cat_id'        =>  '',
                'name'         =>  'You do Not have permission to access this Topic',
                'description'   =>  'You do Not have permission to access this Topic',
                'topics'        =>  $topic_div_list->show()
            ));
            $contentReturn .= $cat_top->show();
            $returnTitle = 'You do Not have permission to access this Topic';

        }
        self::$page_title = $returnTitle;
        return $contentReturn;

    }

/*
	* Get Post in Topics
	* @Since 4.5.1
	* @Param ()
*/
    public function getPostsInTopics():string
    {
        $contentReturn = $posts_list = $returnTitle = '';
        $topic_top = new Template("topic_top.tpl");
        $post_div_list = new Template("post_div_list.tpl");

        $sql_topics = sprintf("SELECT topic_id, topic_subject  FROM  topics WHERE topics.topic_id = '%b';",Input::get('topic_id'));
        $getTopics = $this->_db->query($sql_topics);

        $topics = $getTopics->results()->first();
        if ($this->_user->hasPermission($topics->permission) || ($topics->permission = 'all'  && $this->_user->hasPermission($topics->permission))) {
            if ($getTopics->count()) {
                $sql_posts = sprintf("SELECT posts.post_topic, posts.post_content, posts.post_date, posts.post_by, users.user_id, users.user_name FROM posts LEFT JOIN users ON posts.post_by = users.user_id WHERE posts.post_topic = '%b';",$topics->topic_id);
                $getPosts = $this->_db->query($sql_posts);
                if ($getPosts->count()) {
                    foreach ($getPosts->results() as $posts) {
                        $post_div_list->setArray(array(
                            'post_id'           =>  $posts->post_id,
                            'post_content'      =>  $posts->topic_cat,
                            'post_topic'        =>  $posts->post_topic,
                            'postedby'          =>  $posts->post_by,
                            'username'          =>  $posts->user_name
                        ));
                        $posts_list .= $post_div_list->show();
                    }
                } else {
                    $post_div_list->setArray(array(
                        'topic_id'  =>  '',
                        'topic_cat' =>  '',
                        'title'     =>  'No topic could be displayed.',
                        'date'      =>  '',
                        'postedby'  =>  ''
                    ));
                    $posts_list .= $post_div_list->show();
                }
                $topics = $getTopics->results()->first();
                $topic_top->setArray(array(
                    'topic_id'      =>  $topics->topic_id,
                    'topic_subject'  =>  $topics->topic_subject,
                    'topic_date'    =>  $topics->topic_date,
                    'post'        =>  $posts_list
                ));
                $returnTitle = $topics->topic_subject;
                $contentReturn .= $topic_top->show();
            } else {
                $post_div_list->setArray(array(
                    'post_id'           =>  '',
                    'post_content'      =>   'Could not find Post',
                    'post_topic'        =>  '',
                    'postedby'          =>  '',
                    'username'          =>  ''
                ));

                $topic_top->setArray(array(
                    'topic_id'       =>  '',
                    'topic_subject'  =>  'Could not find Post',
                    'topic_date'    =>  '',
                    'post'          =>  $post_div_list->show()
                ));
                $returnTitle = 'Could not find Post';
                $contentReturn .= $topic_top->show();
            }
        } else {
            $post_div_list->setArray(array(
                'post_id'           =>  '',
                'post_content'      =>   'You do Not have permission to access this Post',
                'post_topic'        =>  '',
                'postedby'          =>  '',
                'username'          =>  ''
            ));

            $topic_top->setArray(array(
                'topic_id'       =>  '',
                'topic_subject'  =>  'You do Not have permission to access this Post',
                'topic_date'    =>  '',
                'post'          =>  $post_div_list->show()
            ));
            $returnTitle = 'You do Not have permission to access this Post';
            $contentReturn .= $topic_top->show();
        }
        self::$page_title = $returnTitle;
        return $contentReturn;
    }
}
