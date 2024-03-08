<?php
namespace App\Helpers;

use App\ForumMessage;
use App\Helpers\BBCodesHelper\Element;
use App\Picture;
use App\Smile;

class BBCodesHelper {

    public static function BBToHTML($text) {
        $text = str_replace(PHP_EOL, "<br>", $text);

        $text = preg_replace('/\[(\/?)(b|i|u|s)\s*\]/', "<$1$2>", $text);

        $text = preg_replace('/\[code\]/', '<pre><code>', $text);
        $text = preg_replace('/\[\/code\]/', '</code></pre>', $text);

        $text = preg_replace('/\[l\]/', '<div style="text-align:left">', $text);
        $text = preg_replace('/\[\/l\]/', '</div>', $text);
        $text = preg_replace('/\[c\]/', '<div style="text-align:center">', $text);
        $text = preg_replace('/\[\/c\]/', '</div>', $text);
        $text = preg_replace('/\[r\]/', '<div style="text-align:right">', $text);
        $text = preg_replace('/\[\/r\]/', '</div>', $text);

        $text = preg_replace('/\[size=(.*?)]/', "<span style='font-size:$1pt'>", $text);
        $text = preg_replace('/\[\/size\]/', '</span>', $text);
        $text = preg_replace('/\[color\=(\S{3,10})]/', "<span style='color:$1'>", $text);
        $text = preg_replace('/\[\/color\]/', '</span>', $text);

        $text = preg_replace('/\[url\](?:http:\/\/)?([a-z0-9-.]+\.\w{2,4})\[\/url\]/', "<a href=\"http://$1\">$1</a>", $text);
        $text = preg_replace('/\[url\s?=\s?([\'"]?)(?:http:\/\/)?([a-z0-9-.]+\.\w{2,4})\1\](.*?)\[\/url\]/', "<a href=\"http://$2\">$3</a>", $text);
        $text = preg_replace('/\[url\](?:https:\/\/)?([a-z0-9-.]+\.\w{2,4})\[\/url\]/', "<a href=\"https://$1\">$1</a>", $text);
        $text = preg_replace('/\[url\s?=\s?([\'"]?)(?:https:\/\/)?([a-z0-9-.]+\.\w{2,4})\1\](.*?)\[\/url\]/', "<a href=\"https://$2\">$3</a>", $text);
        $text = preg_replace('/\[email\](.*?)\[\/email\]/', "<a class='email' href='mailto:$1'>$1</a>", $text);

        $text = preg_replace('/\[hr\]/', '<hr>', $text);
        $text = preg_replace('/\[list\]/', '<ul>', $text);
        $text = preg_replace('/\[\/list\]/', '</ul>', $text);
        $text = preg_replace('/\[\*](.*?)\n/', '<li>$1</li>', $text);
        $text = preg_replace('/\[\*](.*?)\r\n/', '<li>$1</li>', $text);

        $text = preg_replace('/\[img\s*\]([^\]\[]+)\[\/img\]/', "<img src='$1'/>", $text);
        $text = preg_replace('/\[img\s*=\s*([\'"]?)([^\'"\]]+)\1\]/', "<img src='$2'/>", $text);

        $text = preg_replace_callback("/\[spoiler](.*?)\[\/spoiler]/", function($spoilerData) {
            $spoilerContent = $spoilerData[1];
            $length = 6;
            $spoilerId = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
            return '<!--uSpoiler--><div class="uSpoilerClosed" id="uSpoiler'.$spoilerId.'"><div class="uSpoilerButBl"><input type="button" class="uSpoilerButton" onclick="if($(\'#uSpoiler'.$spoilerId.'\')[0]){if ($(\'.uSpoilerText\',$(\'#uSpoiler'.$spoilerId.'\'))[0].style.display==\'none\'){$(\'.uSpoilerText\',$(\'#uSpoiler'.$spoilerId.'\'))[0].style.display=\'\';$(\'.uSpoilerButton\',$(\'#uSpoiler'.$spoilerId.'\')).val(\'Закрыть спойлер\');$(\'#uSpoiler'.$spoilerId.'\').attr(\'class\',\'uSpoilerOpened\');}else {$(\'.uSpoilerText\',$(\'#uSpoiler'.$spoilerId.'\'))[0].style.display=\'none\';$(\'.uSpoilerButton\',$(\'#uSpoiler'.$spoilerId.'\')).val(\'Открыть спойлер\');$(\'#uSpoiler'.$spoilerId.'\').attr(\'class\',\'uSpoilerClosed\');}}" value="Открыть спойлер"/></div><div class="uSpoilerText" style="display:none;"><!--ust-->'.$spoilerContent.'<!--/ust--></div></div><!--/uSpoiler-->';
        }, $text);
        $text = preg_replace_callback("/\[video](.*?)\[\/video]/", function($videoData) {
            $videoLink = $videoData[1];
            $length = 12;
            $videoId = substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ', ceil($length/strlen($x)) )),1, $length);
            return "<!--BBvideo--><span id=\"scr$videoId\"></span><script type=\"text/javascript\">_uVideoPlayer({'url':'$videoLink','width':'640','height':'360'},'scr$videoId');</script><!--/BBvideo-->";
        }, $text);
        $text = preg_replace_callback("/\[url\=(.*?)](.*?)\[\/url]/", function($urlData) {
            return '<!--uSpoiler--><a class="link" href="'.$urlData[1].'" target="_blank">'.$urlData[2].'</a>';
        }, $text);

        $text = preg_replace_callback("/\[quote(.*?)](.*?)\[\/quote]/", function($quoteData) {
            $quoteInfo = explode("=", $quoteData[1]);
            $quoteContent = $quoteData[2];
            if (count($quoteInfo) === 1) {
                return '<!--uzquote--><div class="bbQuoteBlock"><div class="bbQuoteName"><b>Цитата</b></div><div class="quoteMessage"><!--uzq-->'.$quoteContent.'<!--/uzq--></div></div><!--/uzquote-->';
            }
            $quoteParams = explode(";", $quoteInfo[1]);
            if (count($quoteParams) === 1) {
                $quoteName = $quoteParams[0];
                return '<!--uzquote--><div class="bbQuoteBlock"><div class="bbQuoteName"><b>Цитата</b> <span class="qName"><!--qn-->'.$quoteName.'<!--/qn--></span></div><div class="quoteMessage" <!--uzq-->'.$quoteContent.'<!--/uzq--></div></div><!--/uzquote-->';
            }
            $quoteName = $quoteParams[0];
            $message = ForumMessage::find($quoteParams[1]);
            $message_id = $message->id;
            $created_at = $message->created_at_ts;
            $topic_id = $message->topic_id;
            $forum_id = $message->topic->forum_id;
            $link = "/forum/$forum_id-$topic_id-$message_id-$created_at";
            return '<!--uzquote--><div class="bbQuoteBlock"><div class="bbQuoteName"><b>Цитата</b> <span class="qName"><!--qn-->'.$quoteName.'<!--/qn--></span> (<span class="qAnchor"><!--qa--><a href="'.$link.'"><img alt=""  title="Ссылка на цитируемый текст" src="/.s/img/fr/ic/11/lastpost.gif"></a><!--/qa--></span>) </div><div class="quoteMessage"><!--uzq-->'.$quoteContent.'<!--/uzq--></div></div><!--/uzquote-->';
        }, $text);

        $smiles = Smile::where('text', '!=', '')->get();
        foreach ($smiles as $smile) {
            $text = str_replace($smile->text, "<img class='smile' src='".$smile->picture->url."' />", $text);
        }
        return $text;
    }

    public static function HTMLToBB($text) {
        if ($text == "") {
            return "";
        }

        $text = preg_replace('/<!--(.*)-->/Uis', '', $text);
        $document = new \DOMDocument();
        try {
            libxml_use_internal_errors(true);
            $document->loadHTML('<?xml encoding="UTF-8">' . $text);
            $document->encoding = 'UTF-8';
            $root = $document->getElementsByTagName('html')->item(0);
            $rootElement = new Element($root);

            foreach ($rootElement->getChildren() as $child) {

                self::convertChildren($child);
            }
            $bbcode = self::convertToBBCode($rootElement);
            return trim($bbcode);
        } catch(\Exception $e) {
            return $text;
        }
    }

    private static function convertChildren(Element $element)
    {
        if ($element->hasChildren()) {
            foreach ($element->getChildren() as $child) {
                self::convertChildren($child);
            }
        }
        $bbcode = self::convertToBBCode($element);
        if (is_array($bbcode)) {
            $element->setFinalBBCode($bbcode[0]);
        } else {
            $element->setFinalBBCode($bbcode);
        }


    }

    protected static function convertToBBCode(Element $element)
    {
        $tag = $element->getTagName();
        if ($tag === "#text" || $tag === "#cdata-section") {
            return html_entity_decode($element->getChildrenAsString());
        } elseif (in_array($tag, ['b', 'i', 'u', 's'])) {
            return "[".$tag."]".$element->getValue()."[/".$tag."]";
        } elseif ($tag == "a") {
            $value = $element->node->getAttribute('href');
            if (strpos($value, "mailto") != false) {
                $address = explode("mailto:", $value);
                $mail = $address[count($address) - 1];
                return "[email]".$mail."[/email]";
            } else {
                return "[url=".$value."]".$element->getValue()."[/url]";
            }
        } elseif ($tag === "p" || $tag === "body" || $tag === "html" || $tag === "input") {
            return $element->getValue();
        } elseif ($tag === "li") {
            return "[*]".$element->getValue();
        } elseif ($tag === "ul") {
            return "[list]".$element->getValue()."[/list]";
        } elseif ($tag === "div") {
            $style = $element->node->getAttribute('style');

            if (!$style || $style == "") {
                $class = $element->node->getAttribute('class');
                if ($class === "uSpoilerButBl") {
                    return "";
                } elseif ($class === "uSpoilerClosed") {
                    $spoilerContents = $element->getChildren()[1];
                    return "[spoiler]".$spoilerContents->getValue()."[/spoiler]";
                } elseif ($class === "bbQuoteBlock") {
                    $quote = $element->getChildren()[0]->node->wholeText;

                    $regex = "/(.*?)<span>(.*?)<\/span> \((.*?)\[url=(.*?)\]\[img](.*?)\[\/img]\[\/url\]<\/span>\) (.*?)/";
                    preg_match($regex, $quote, $matches);
                    if (count($matches) == 0) {
                        $regex = "/(.*?)<span>(.*?)<\/span>\)(.*?)/";
                        preg_match($regex, $quote, $matches);
                        if (count($matches) === 4) {
                            $regex = '/\/(.*?)<span>(.*?)<\/span> \((.*?)\[url=(.*?)\]\[img](.*?)\[\/img]\[\/url\]<\/span>\)/';
                            preg_match($regex, $quote, $matches);
                            if (count($matches) === 0) {
                                $regex = '/\/(.*?)<span>(.*?)<\/span>\)(.*)/';
                                preg_match($regex, $quote, $matches);
                                $name = $matches[2];
                                $text = $matches[3];

                                return "[quote=" . $name . "]" . $text . "[/quote]";
                            } else {
                                $text = preg_split($regex, $quote)[1];
                                $name = $matches[2];
                                $message_id = explode("/", $matches[4]);
                                $message_id = explode("-", $message_id[(count($message_id) - 1)]);
                                $message_id = $message_id[2];
                                return "[quote=" . $name . ";" . $message_id . "]" . $text . "[/quote]";
                            }
                        } else {
                            $text = preg_split($regex, $quote);
                            if (isset($text[1])) {
                                $name = $matches[2];
                                return "[quote=" . $name . "]" . $text[1] . "[/quote]";
                            } else {
                                $text = preg_split('/\[b]Цитата\[\/b](.*?)/', $quote);
                                if (isset($text[1])) {
                                    return "[quote]" . $text . "[/quote]";
                                }
                                return $text;
                            }
                        }
                    } else {
                        $text = preg_split($regex, $quote)[1];
                        $name = $matches[2];
                        $message_id = explode("/", $matches[4]);
                        $message_id = explode("-", $message_id[count($message_id) - 1]);
                        $message_id = $message_id[2];
                        return "[quote=".$name.";".$message_id."]".$text."[/quote]";
                    }
                }
            } else {
                $attr = explode(":", $style);
                if ($attr[0] === "text-align") {
                    $tags = ["left" => "l", "center" => "c", "right" => "r"];
                    if (isset($tags[$attr[1]])) {
                        return "[" . $tags[$attr[1]] . "]" . $element->getValue() . "[/" . $tags[$attr[1]] . "]";
                    }
                }
            }
            return $element->getValue();
        } elseif ($tag === "img") {
            $src = $element->node->getAttribute('src');
            $class = $element->node->getAttribute('class');
            //if ($class == "smile") {
                $picture = Picture::where(['url' => $src])->first();
                if ($picture) {
                    $smile = Smile::where(['picture_id' => $picture->id])->first();
                    if ($smile) {
                        return $smile->text;
                    }
                }
            //}
            return "[img]".$src."[/img]";
        } elseif ($tag === "span") {
            $attr = $element->node->getAttribute('style');
            $property = explode(":", $attr);
            if ($property[0] == "font-size") {
                return "[size=".((int)$property[1])."]".$element->getValue()."[/size]";
            } elseif ($property[0] == "color") {
                return "[color=".$property[1]."]".$element->getValue()."[/color]";
            } else {
                $class = $element->node->getAttribute('class');
                if ($class === "qName" || $class === "qAnchor") {
                    return "<span>".$element->getValue()."</span>";
                }
            }
        } elseif ($tag === "script") {
            $value = $element->getValue();
            if (strpos($value, "_uVideoPlayer") !== false) {
                preg_match('/_uVideoPlayer\({\'url\':\'(.*?)\',(.*?)/', $value, $matches);
                $video = $matches[1];
                return "[video]".$video."[/video]";
            } else {
                return $element->getValue();
            }
        } elseif ($tag == "br") {
            return PHP_EOL;
        } elseif ($tag == "hr") {
            return "[hr]";
        } else {
           // dd($tag);
            return $element->getValue();
        }
       // $converter = $this->environment->getConverterByTag($tag);
       // return $converter->convert($element);
    }

}
