<?php
namespace App\Commands\Tools;
class Tools
{
    public static function parseCallback($matches)
    {
        /*
            $mathes array
            * 1 - An extra [ to allow for escaping shortcodes with double [[]]
             * 2 - The shortcode name
             * 3 - The shortcode argument list
             * 4 - The self closing /
             * 5 - The content of a shortcode when it wraps some content.
             * 6 - An extra ] to allow for escaping shortcodes with double [[]]
         */
        // allow [[player]] syntax for escaping the tag
        if ($matches[1] == '[' && $matches[6] == ']') {
            return substr($matches[0], 1, -1);
        }
        //还原转义后的html
        //[dplayer title=&quot;Test Abc&quot; artist=&quot;haha&quot; id=&quot;1234543&quot;/]
        $tag = htmlspecialchars_decode($matches[3]);
        //[dplayer]标签的属性，类型为array
        $attrs = self::shortcode_parse_atts($tag);
        return $attrs;
    }

    private static function shortcode_parse_atts($text)
    {
        $atts = array();
        $pattern = '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|(\S+)(?:\s|$)/';
        $text = preg_replace("/[\x{00a0}\x{200b}]+/u", " ", $text);
        if (preg_match_all($pattern, $text, $match, PREG_SET_ORDER)) {
            foreach ($match as $m) {
                if (!empty($m[1]))
                    $atts[strtolower($m[1])] = stripcslashes($m[2]);
                elseif (!empty($m[3]))
                    $atts[strtolower($m[3])] = stripcslashes($m[4]);
                elseif (!empty($m[5]))
                    $atts[strtolower($m[5])] = stripcslashes($m[6]);
                elseif (isset($m[7]) && strlen($m[7]))
                    $atts[] = stripcslashes($m[7]);
                elseif (isset($m[8]))
                    $atts[] = stripcslashes($m[8]);
            }

            // Reject any unclosed HTML elements
            foreach ($atts as &$value) {
                if (false !== strpos($value, '<')) {
                    if (1 !== preg_match('/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value)) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim($text);
        }
        return $atts;
    }

    public static function get_shortcode_regex($tagnames = null)
    {
        $tagregexp = join('|', array_map('preg_quote', $tagnames));

        // WARNING! Do not change this regex without changing do_shortcode_tag() and strip_shortcode_tag()
        // Also, see shortcode_unautop() and shortcode.js.
        return
            '\\['                              // Opening bracket
            . '(\\[?)'                           // 1: Optional second opening bracket for escaping shortcodes: [[tag]]
            . "($tagregexp)"                     // 2: Shortcode name
            . '(?![\\w-])'                       // Not followed by word character or hyphen
            . '('                                // 3: Unroll the loop: Inside the opening shortcode tag
            . '[^\\]\\/]*'                   // Not a closing bracket or forward slash
            . '(?:'
            . '\\/(?!\\])'               // A forward slash not followed by a closing bracket
            . '[^\\]\\/]*'               // Not a closing bracket or forward slash
            . ')*?'
            . ')'
            . '(?:'
            . '(\\/)'                        // 4: Self closing tag ...
            . '\\]'                          // ... and closing bracket
            . '|'
            . '\\]'                          // Closing bracket
            . '(?:'
            . '('                        // 5: Unroll the loop: Optionally, anything between the opening and closing shortcode tags
            . '[^\\[]*+'             // Not an opening bracket
            . '(?:'
            . '\\[(?!\\/\\2\\])' // An opening bracket not followed by the closing shortcode tag
            . '[^\\[]*+'         // Not an opening bracket
            . ')*+'
            . ')'
            . '\\[\\/\\2\\]'             // Closing shortcode tag
            . ')?'
            . ')'
            . '(\\]?)';                          // 6: Optional second closing brocket for escaping shortcodes: [[tag]]
    }

    public function RecontentHtml($contentHtml,$content){
        $wavurl = "";
        $url = "";
        $bvid = "";

        $arrPattern[] = "/\[dplay\]\s*(.*?)\s*\[\/dplay\]/i";
        preg_match_all("/\[dplay\]\s*(.*?)\s*\[\/dplay\]/is", $content, $del);

        $pattern = $this->get_shortcode_regex(['dplay']);
        preg_match("/$pattern/", $content, $matches);
//        $contentHtml = $model->formatContent();

        if ($matches){
            $text = $this->parseCallback($matches);
            $url = $text["url"];
            $bvid = $text["bvid"];
//            $url = $this->OSS_CDN_sign($url,true);
            $contentHtml = preg_replace( "/$pattern/" ,'<div id="dplayer"></div>',$contentHtml );
        }

        $arrPattern2[] = "/\[audio\]\s*(.*?)\s*\[\/audio\]/i";
        preg_match_all("/\[audio\]\s*(.*?)\s*\[\/audio\]/is", $content, $del);

        $pattern2 = $this->get_shortcode_regex(['audio']);
        preg_match("/$pattern2/", $content, $matches2);
        if ($matches2){
            $text = $this->parseCallback($matches2);
            $wavurl = $text["url"];
            $contentHtml = preg_replace( "/$pattern2/" ,'<div id="aplayer"></div>',$contentHtml );
        }

        $pattern3 = $this->get_shortcode_regex(['bil']);
        preg_match("/$pattern3/", $content, $matches3);

//        if ($matches3){
//            $text = $this->parseCallback($matches3);
//            $bvid = $text["bvid"];
//            $api = "https://api.cathyy.com/api/getbilurl";
//            $post["vid"] = $bvid;
//            $post["api_sign"] = $this->getapisgin($post);
//            $cdata = $this->curl_request($api, json_encode($post),1);
//            $cdata = json_decode($cdata,true);
//            file_put_contents('./1.txt', "getbilurl".json_encode($cdata)."\r\n", FILE_APPEND);
//            $url = $cdata["data"]["play_url"];
//            $contentHtml = preg_replace( "/$pattern3/" ,'',$contentHtml );
//        }

        return array(
            "wavurl"=>$wavurl,
            "vurl"=>$url,
            "bvid"=>$bvid,
            "contentHtml"=>$contentHtml,
        );
    }

    public function getSimilar($x,$y){
        $api = "https://api.cathyy.com/api/getsim";
        $post["c1"] = $x;
        $post["c2"] = $y;
        $post["api_sign"] = $this->getapisgin($post);
        $cdata = $this->curl_request($api, json_encode($post),1);
        $cdata = json_decode($cdata,true);
        file_put_contents('./1.txt', "getSimilar".json_encode($cdata)."\r\n", FILE_APPEND);
        return $cdata["data"];
    }

    public function getsimtext($title,$list){
        $api = "https://api.cathyy.com/api/getsimtext";
        $post["title"] = $title;
        $post["list"] = $list;
        $cdata = $this->curl_request($api, json_encode($post),1);
        $cdata = json_decode($cdata,true);
        file_put_contents('./1.txt', "getsimtext".json_encode($cdata)."\r\n", FILE_APPEND);
        return $cdata["data"];
    }

    public function getapisgin($data){
        ksort ( $data );
        $canonicalizedQueryString = '';
        foreach ( $data as $key => $value ) {
            $canonicalizedQueryString .= $this->percentEncode ( $key ) . $this->percentEncode ( $value );
        }
        return md5($canonicalizedQueryString.$this->showapi_sign);
    }
}
