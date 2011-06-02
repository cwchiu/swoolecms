<?php
require WEBPATH.'/apps/controllers/UserBase.php';
class myphoto extends UserBase
{
    /**
     * 相片的呈现
     * @return unknown_type
     */
    function index()
    {
        $param['uid'] = $this->uid;
        $photo = $this->swoole->model->UserPhoto->gets($param);
        $param['select'] = 'count(id) as c';
        $countphoto = $this->swoole->model->UserPhoto->gets($param);
        $this->swoole->tpl->assign('photo',$photo);
        $this->swoole->tpl->assign('count',$countphoto);
        $this->swoole->tpl->display('myphoto_index.html');
    }
    /**
     * 用flash添加照片
     */
    function add_photo()
    {
        if($_FILES)
        {
            global $php;
            $php->autoload('upload');
            $php->upload->access = 'jpg,gif,png';
            $php->upload->thumb_width = 136;
            $php->upload->thumb_height = 136;
            $php->upload->max_width = 500;
            $php->upload->max_height = 500;
            $php->upload->sub_dir = 'user_images';
            $up_pic = $php->upload->save('Filedata');
            if(empty($up_pic))
            {
                echo '上传失败，请重新上传！';
                exit;
            }
            $data['picture'] = $up_pic['name'];
            $data['imagep'] = $up_pic['thumb'];
            $data['uid'] = $_POST['uid'];
            $this->swoole->model->UserPhoto->put($data);
            if(isset($_POST['post'])) $this->swoole->model->Feeds->send('photo',$data['uid']);
        }
        else $this->swoole->tpl->display('myphoto_add_photo.html');
    }
    function show()
    {
        $pid = (int)$_GET['id'];
        $param['uid'] = $this->uid;
        $param['id'] = $pid;
        $param['limit'] = 1;
        $photo = $this->swoole->model->UserPhoto->gets($param);

        if(empty($photo[0]['id']))
        {
        	Swoole_js::js_back('还没有上传照片！');
        	exit;
        }
        $photo = $photo[0];

        $param1['uid'] = $this->uid;
        $param1['where'] = "id>".$pid;
        $param1['select'] = 'id';
        $param1['order'] = 'id asc';
        $param1['limit'] = 1;
        $nextid = $this->swoole->model->UserPhoto->gets($param1);

        if(empty($nextid))
        {
            $first['uid'] = $param['uid'];
            $first['limit'] = 1;
            $first['order'] = 'id ASC';
            $first['select'] = 'id';
            $nextid = $this->swoole->model->UserPhoto->gets($first);
        }

        $param2['uid'] = $param['uid'];
        $param2['where'] = 'id<'.$pid;
        $param2['select'] = 'id';
        $param2['limit'] = 1;
        $perid = $this->swoole->model->UserPhoto->gets($param2);

        if(empty($perid))
        {
            $second['uid'] = $param['uid'];
            $second['limit'] = 1;
            $second['select'] = 'id';
            $second['order'] = 'id DESC';
            $perid = $this->swoole->model->UserPhoto->gets($second);
        }
        $this->swoole->tpl->assign('perid',$perid);
        $this->swoole->tpl->assign('nextid',$nextid);
        $this->swoole->tpl->assign('photo',$photo);
        $this->swoole->tpl->display();
    }
}