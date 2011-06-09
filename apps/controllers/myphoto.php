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
        $gets['uid'] = $this->uid;
        $gets['select'] = 'id,imagep';
        $gets['page'] = empty($_GET['page'])?1:(int)$_GET['page'];
        $gets['pagesize'] =15;

        $photo = $this->swoole->model->UserPhoto->gets($gets,$pager);
        $this->swoole->tpl->assign('photo',$photo);
        $this->swoole->tpl->assign('count',$pager->total);
        $this->swoole->tpl->assign('pager',$pager->render());
        if(isset($_GET['from'])) $this->swoole->tpl->display('myphoto_insert.html');
        else $this->swoole->tpl->display('myphoto_index.html');
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
        Widget::photoDetail($pid,$this->uid);
        $this->swoole->tpl->display();
    }
}