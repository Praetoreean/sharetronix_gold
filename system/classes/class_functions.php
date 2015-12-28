<?php
    class functions{
        public static function process_smile($message){
            //Getting Smiles data And Find them In text
            //Replace It With His Css Class

            foreach($GLOBALS['C']->POST_ICONS as $k=>$v) {
                $exp = explode('_',$v);//Get Parent Class

                $parent_class = $exp[1];

                //$txt = '<span class="smile-'.$parent_class.' smile-'.$v.'"></span>';//Creating Span Class
                /**
                 * Replace With How To Process Smile
                 */
                $message	= str_replace($k, $txt, $message);//Replacing With Span
            }

            return $message;
        }
    }