<?php
    class functions{
        public static function process_smile($message){
            //Getting Smiles data And Find them In text
            //Replace It With His Css Class
            foreach($GLOBALS['C']->POST_ICONS as $k=>$v) {

                //$txt = '<span class="smile-'.$parent_class.' smile-'.$v.'"></span>';//Creating Span Class
                /**
                 * Replace With How To Process Smile
                 */
                $txt	= '<img src="'.$GLOBALS['C']->IMG_URL.'icons/'.$v.'" class="post_smiley" alt="'.$k.'" title="'.$k.'" />';
                $message	= str_replace($k, $txt, $message);
            }

            return $message;
        }
    }