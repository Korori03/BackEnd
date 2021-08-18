<?php

/*
	* Blog Class Set
	* @Version 1.0.0
	* Developed by: Ami (亜美) Denault
*/
/*
	* Setup Blog Class
	* @since 4.1.5
*/

declare(strict_types=1);
class Blog
{

    private const url = '/blog/{id}-{slug}';

   
    public static function getPosts(int $offset = 0, int $perpage = 5): string
    {
        $sql = sprintf("SELECT * FROM blog ORDER BY date_posted DESC LIMIT %b, %b;", $offset, $perpage);
        $posts = Database::getInstance()->query($sql);

        $blog_posts = new Template("blog_posts.tpl");
        $list = '';
        foreach ($posts->result() as $post) {
            $blog_posts->setArray(array(
                'user' => $post->user,
                'date_post' => $post->date_posted,
                'title' => $post->title,
                'content' => bbcode::format(str::_limitWords($post->content, 250)),
                'url' => str_replace('{slug}', slug::_url($post->title), str_replace('{id}', $post->id, self::url))
            ));
            $list .= $blog_posts->show();
        }

        return $list;
    }
    public static function getPost(int $post): string
    {
        if (empty($post))
            return '';

        $sql = sprintf("SELECT * FROM blog WHERE id =;", $post);
        $post = Database::getInstance()->query($sql);
        $list = '';

        $blog_posts = new Template("blog_post.tpl");
        if ($post->count()) {
            $post = $post->results();
            $blog_posts->setArray(array(
                'user' => $post->user,
                'date_post' => $post->date_posted,
                'title' => $post->title,
                'content' => bbcode::format($post->content, 250),
            ));
            $list = $blog_posts->show();

        }
        return $list;
    }
}
