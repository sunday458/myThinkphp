<?php
namespace User\Controller;
use Think\Controller;

/**
* 
*/
class TestController extends Controller
{

	public function __construct()
	{
		parent::__construct();
	}

    public function upload_file()
    {
        echo $_SERVER['DOCUMENT_ROOT'];
        echo '<br>';
        echo __ROOT__;
        $this->display();
    }

    public function upload(){    

        $upload = new \Think\Upload();
        // 实例化上传类    
        $upload->maxSize   =     3145728 ;
        // 设置附件上传大小    
        $upload->exts      =     array('jpg', 'gif', 'png', 'jpeg');
        // 设置附件上传类型    
        $upload->rootPath  =     './Public/';
        $upload->savePath  =     './Uploads/'; // 设置附件上传目录   
        $upload->saveName  =     'pic'.date('YmdHis'); 
        $upload->subName   =     ''; //上传是否自动创建子目录
        //var_dump($upload);exit();
        // 上传文件     
        $info   =   $upload->upload();    
        if(!$info) {
        // 上传错误提示错误信息       
            $this->error($upload->getError());    
        }else{
            // 上传成功        
            //$this->success('上传成功！');    
            foreach($info as $file){        
                echo $file['savepath'].$file['savename'];    
            }
        }
    }

    public function index(){
        //地区
        $area=M('citys')->where('ParentId=100000')->select();
        $this->assign('area',$area);
        $this->display();
    }
    
    public function getCity(){
        $ParentId=I('post.ParentId');
        $current_city=M('citys')->where('ParentId='.$ParentId)->select();
        $data['data']=$current_city;
        $this->ajaxReturn($data);
    }
    
    public function getCounty(){
        $ParentId=I('post.ParentId');
        $current_county=M('citys')->where('ParentId='.$ParentId)->select();
        $data['data']=$current_county;
        $this->ajaxReturn($data);
    }

    public function get_area_data()
    {
        $p_id =I('p_id');
        $p_id = $p_id?$p_id:440000;
        $pro_model =M('province','hat');
        $sql = " SELECT p.provinceID,p.province,c.cityID,.c.city,c.father AS city_parent,a.areaID,a.area,a.father AS area_parent FROM `hat_province` p 
LEFT JOIN `hat_city` c ON c.father = p.provinceID LEFT JOIN `hat_area` a ON a.father = c.cityID WHERE provinceID = $p_id ";
        $list = $pro_model->query($sql);
        var_dump($list);
    }



}