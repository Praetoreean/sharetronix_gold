<?php
    class chat{
        private $network;
        private $page;
        private $user;
        private $cache;
        private $db1;
        private $db2;


        private $error;
        private $to_user;


        public function __construct(){
            $this->error = false;

            $n = $GLOBALS['network'];

            if($n->id){
                $this->network = $n;
                $this->page = $GLOBALS['page'];
                $this->user = $GLOBALS['user'];
                $this->db1 = $GLOBALS['db1'];
                $this->db2 = $GLOBALS['db2'];
                $this->cache = $GLOBALS['cache'];

                if($this->user->is_logged){
                    $this->error = false;
                    return;
                }
                $this->error = true;
                return;
            }
            $this->error = true;
        }

        public function insert($message,$to_user=false,$to_group=false,$reply_to = false){

            if($this->error){
                return false;
            }
            $message = trim($message);
            if(empty($message)){
                return false;
            }


            $message = mb_substr($message,0,$GLOBALS['C']->POST_MAX_CHAT_MESSAGE);


            //To User Code
            //To Group Code
            //Reply To Code

            if($reply_to != false){
                $reply_to_str = ',reply_to="'.intval($reply_to).'"';
            }else{
                $reply_to_str = '';
            }
            $this->db2->query('INSERT INTO chat SET user_id="'.$this->user->id.'",message="'.$this->db2->e($message).'",date="'.time().'"'.$reply_to_str);


            return $this->db2->insert_id();
        }
        public function get_message($lastdate,$limit = false,$not_this_user =  false){
            if($this->error){
                return false;
            }

            $lastdate = (int) $lastdate;
            if($lastdate == 0){
                return false;
            }
            $limit_str = '';
            if($limit){
                $limit = (int)$limit;
                if($limit == 0){
                    $limit_str = '';
                }else{
                    $limit_str = ' LIMIT '.$limit;
                }
            }
            if($not_this_user){
                $not_this_user_str = 'user_id != "'.$this->user->id.'" AND';
            }else{
                $not_this_user_str = '';
            }

            $query = $this->db2->query('SELECT id,user_id,message,date,reply_to FROM chat WHERE '.$not_this_user_str.' date > "'.$this->db2->e($lastdate).'" AND is_deleted = "0" ORDER BY id DESC'.$limit_str);

            $chat = array();
            $i = 0;
            $lastdate = 0;
            $reply_to = array();
            while($o = $this->db2->fetch_object($query)){
                if($i == 0){
                    $lastdate = $o->date;
                }
                if($tmp = $this->network->get_user_by_id($o->user_id)){
                    $o->user = $tmp;
                }else{
                    continue;
                }
                $i++;

                $o->message = htmlspecialchars($o->message);
                if( FALSE!==strpos($o->message,'http://') || FALSE!==strpos($o->message,'http://') || FALSE!==strpos($o->message,'ftp://') ) {
                    $o->message	= preg_replace_callback('#(^|\s)((http|https|ftp)://\w+[^\s\[\]]+)#i',
                        function($m) { return post::_postparse_build_link($m[2], $m[1]); }
                        , $o->message);
                }

                $o->message = functions::process_smile($o->message);
                $chat[$o->id]['chat_id'] = $o->id;
                $chat[$o->id]['user_id'] = $o->user_id;
                $chat[$o->id]['avatar'] = $GLOBALS['C']->IMG_URL.'avatars/thumbs3/'.$o->user->avatar;
                $chat[$o->id]['fullname'] = htmlspecialchars($o->user->fullname);
                $chat[$o->id]['userlink'] = userlink($o->user->username);
                $chat[$o->id]['message'] = $o->message;
                $chat[$o->id]['date'] = pdate('H:i',$o->date);
                $chat[$o->id]['is_seen'] = 1;
                $chat[$o->id]['reply_to'] = $o->reply_to;
                if($o->reply_to > 0){
                    $reply_to[] = $o->reply_to;
                }

            }
            if(count($reply_to) > 0 ){
                $reply_to_query = $this->db2->query('SELECT id,user_id,message,date FROM chat WHERE id IN ('.implode(",",$reply_to).')');
                while($x = $this->db2->fetch_object($reply_to_query)){
                    foreach($chat as $k=>$v){
                        if($v['reply_to'] > 0 && intval($v['reply_to']) == $x->id ){
                            if($tmp = $this->network->get_user_by_id($x->user_id)){
                                $chat[$k]['reply_to_data'] = array(
                                    'fullname'=>$this->network->get_user_by_id($x->user_id)->fullname,
                                    'message'=> str_cut($x->message,50),
                                );
                            }
                        }
                    }
                }
            }


            $this->update_seens();


            $chat = array_reverse($chat);


            $data = array();
            $data[0] = $lastdate;
            $data[1] = $chat;

            return $data;
        }
        public function update_seens(){
            if($this->error){
                return false;
            }
            $this->db2->query('UPDATE chat SET is_seen = 1');
            return true;
        }


        public function delete_chat($id){
            if($this->error){
                return false;
            }
            if(!$this->user->info->is_network_admin){
                return false;
            }
            $id = intval($id);
            if($id == 0){
                return false;
            }
            $this->db2->query('DELETE FROM chat WHERE id="'.$id.'"');
            return true;
        }




    }