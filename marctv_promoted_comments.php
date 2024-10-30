<?php
/*
  Plugin Name: MarcTV Promoted Comments
  Plugin URI: http://www.marc.tv/blog/marctv-wordpress-plugins/
  Description: Allows the author to promote comments and displays them below the post.
  Version: 1.3
  Author: MarcDK
  Author URI: http://marc.tv
  License: GPL2
 */


class PromotedComments
{

    private $version = '1.3';

    public function __construct()
    {
        $this->frontendInit();
    }

    public function frontendInit()
    {

        load_plugin_textdomain('marctv-promoted-comments', false, dirname(plugin_basename(__FILE__)) . '/language/');
        add_filter('the_content', array($this, 'add_promoted_content'), 10, 3);

    }

    public function stripBlockquoteTags($html){

        $doc = new DOMDocument("1.0", "utf-8");
        libxml_use_internal_errors(true);
        $doc->loadHTML(mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8'));
        $xpath = new DOMXPath($doc);
        foreach ($xpath->query('//blockquote') as $node) {
            $node->parentNode->removeChild($node);
        }
        $html = $doc->saveHTML();

        return $html;

    }

    public function render_comments($cids_arr)
    {
        $html = '<h2>'. __("promoted comments", 'marctv-promoted-comments') . '</h2>';
        $html .= '<p>'. __("Curated comments hand-picked from the commments section of this article. A click reveals the original comment.", 'marctv-promoted-comments')  . '</p>';
        foreach($cids_arr as $cid){

            $comment_text = get_comment_text($cid);

            $stripped_comment_content = strip_tags($this->stripBlockquoteTags($comment_text));

            $html .= '<blockquote class="promoted-comment"><p>';
            $html .= '<a href=' . get_comment_link($cid) . '>„' . wp_trim_words($stripped_comment_content,25    ) . '“</a>';
            $html .= '<cite><a href=' . get_comment_link($cid) . '>' . get_comment_author($cid) . '</a></cite></p>';
            $html .= '</blockquote>';
            $html .= '';
        }

        return $html;
    }

    public function add_promoted_content($content)
    {
        if (is_single()) {
            $id = get_the_ID();
            $cids = get_post_meta($id, 'promoted_comments', true);
            $cids_arr = explode(',', $cids);

            if ( $cids && count($cids_arr) > 0 ) {
                $prommoted_comments = $this->render_comments($cids_arr);
                return $content . $prommoted_comments;
            }
        }

        return $content;
    }
}


new PromotedComments();

?>