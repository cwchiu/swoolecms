<?php
/**
 * 照片数据库相关查询类
 * @author guoerbo
 * 2009-07-18
 */
class UserPhoto extends Model
{
//    var $table = 'user_album';
    public $table = 'user_picture';
    /**
     * 注释掉的部分是带有相册功能的相片相关操作
     */
//    /**
//     * 查找分页相册
//     */
//    public function findalbum($userid,$pagenb,$perpage) {
//        $quer = $this->db->query("select * from user_album where uid = '".$userid."' limit ".($pagenb-1)*$perpage.",".$perpage."");
//        $out = $quer->fetchAll();
//        for($i=0;$i<count($out);$i++) {
//            $quer = $this->db->query("select imagep from user_picture where aid = '".$out[$i]['id']."' limit 1");
//            $imagep = $quer->fetch();
//            if($imagep) {
//                $out[$i] = $out[$i]+$imagep;
//            }else {
//                $out[$i]['imagep'] = DEFAULTIMAGE;
//            }
//        }
//        return $out;
//    }
//    /**
//     * 相册统计
//     */
//    public function countalbum($userid) {
//        $quer = $this->db->query("select count(id) as n from user_album where uid = '".$userid."'");
//        $out = $quer->fetch();
//        return $out['n'];
//    }
//    /**
//     * 查找分页相片
//     */
//    public function findimage($aid,$uid,$pagenb,$perpage) {
//        $quer = $this->db->query("select * from user_picture where aid = '".$aid."' and uid = '".$uid."' limit ".($pagenb-1)*$perpage.",".$perpage."");
//        return $quer->fetchAll();
//    }
//    /**
//     * 相片统计
//     */
//    public function countimage($aid,$uid) {
//        $quer = $this->db->query("select count(id) as n from user_picture where aid = '".$aid."' and uid = '".$uid."'");
//        $out = $quer->fetch();
//        return $out['n'];
//    }
//    /**
//     * 添加相片
//     */
//    public function addphoto($aid,$uid,$imagep,$picture,$title) {
//        $quer = $this->db->query("select count(id) as n from user_album where id = '".$aid."'");
//        $out = $quer->fetch();
//        if($out['n'] != 0) {
//            $quer1 = $this->db->query("select num from user_album where id = '".$aid."'");
//            $out1 = $quer1->fetch();
//            $num = $out1['num']+1;
//            $this->db->query("update user_album set num = '".$num."' where id = '".$aid."'");
//            $this->db->query("insert into user_picture (aid,uid,imagep,picture,title) values ('".$aid."','".$uid."','".$imagep."','".$picture."','".$title."')");
//            return true;
//        }else {
//            return false;
//        }
//    }
//    /**
//     * 查看相片
//     */
//    public function findpicture($id,$uid) {
//        $quer = $this->db->query("select * from user_picture where id = '".$id."' and uid = '".$uid."'");
//        return $quer->fetchAll();
//    }
//    /**
//     * 查找相册里上一张相片
//     */
//    public function findper($id,$aid,$uid) {
//        $quer = $this->db->query("select id from user_picture where aid = '".$aid."' and uid = '".$uid."' and id < '".$id."' order by id desc limit 1");
//        return $quer->fetch();
//    }
//    /**
//     *查找相册里下一张照片
//     */
//    public function findnex($id,$aid,$uid) {
//        $quer = $this->db->query("select id from user_picture where aid = '".$aid."' and uid = '".$uid."' and id > '".$id."' limit 1");
//        return $quer->fetch();
//    }
//    /**
//     * 删除照片
//     */
//    public function deletephoto($id,$uid,$aid) {
//        $quer = $this->db->query("select imagep, picture from user_picture where id = '".$id."' and uid = '".$uid."'");
//        $out = $quer->fetch();
//        if($out) {
//            $quer1 = $this->db->query("select num from user_album where id = '".$aid."'");
//            $out1 = $quer1->fetch();
//            $num = $out1['num']-1;
//            $this->db->query("update user_album set num = '".$num."' where id = '".$aid."'");
//            unlink(WEBPATH.$out['imagep']);
//            unlink(WEBPATH.$out['picture']);
//            $this->db->query("delete from user_picture where id = '".$id."' and uid = '".$uid."'");
//            return true;
//        }else {
//            return false;
//        }
//    }
//    /**
//     * 删除相册以及相册里面照片
//     */
//    public function deletealbum($aid,$uid) {
//        $quer = $this->db->query("select id from user_album where id = '".$aid."' and uid = '".$uid."'");
//        $out = $quer->fetchAll();
//        if($out) {
//            $quer = $this->db->query("select id,imagep,picture from user_picture where aid = '".$aid."' and uid = '".$uid."'");
//            $outone = $quer->fetchAll();
//            foreach($outone as $arr) {
//                unlink(WEBPATH.$arr['imagep']);
//                unlink(WEBPATH.$arr['picture']);
//                $this->db->query("delete from user_picture where id = '".$arr["id"]."' and uid = '".$uid."'");
//            }
//            $this->db->query("delete from user_album where id = '".$aid."' and uid = '".$uid."'");
//            return true;
//        }else {
//            return false;
//        }
//    }
//    /**
//     * 获取相册信息
//     */
//    public function getalbum($album) {
//        $quer = $this->db->query("select id,title,intro from user_album where id = '".$album['id']."' and uid = '".$album['uid']."'");
//        return $quer->fetchAll();
//    }
//    /**
//     * 更新相册信息
//     */
//    public function editalbum($album) {
//        $quer = $this->db->query("select id from user_album where id = '".$album['id']."' and uid = '".$album['uid']."'");
//        $out = $quer->fetchAll();
//        if($out) {
//            $this->db->query("update user_album set title = '".$album['title']."',intro = '".$album['intro']."' where id = '".$album['id']."'");
//            return true;
//        }else {
//            return false;
//        }
//    }
}
?>