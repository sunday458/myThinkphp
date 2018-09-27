<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Document</title>
	<style type="text/css">
		.categoryLevel .spread {
		    /*position: absolute;
		    left: 2px;
		    top: 0;*/
		    color: #ff0000;
		    font-size: 24px;
		    cursor: pointer;
		}
    </style>
    <!--引入CSS-->
<link rel="stylesheet" type="text/css" href="/mythinkphp/public/js/webuploader/webuploader.css">
</head>
<body>
    <br/>
    <!-- <form action="<?php echo U('activity/test_checkbox');?>" method="post">
            <div class="categoryLevel">
                 
            </div>
        <input type="submit" value="Submit" />
    </form> -->
    <br/>
    <!-- <form action="<?php echo U('activity/test_submit');?>" method="post">
    <input type="text" name="my_text">
    <input type="submit" name="mySubmit" value="入职">
    <input type="submit" name="mySubmit" value="离职">
    <input type="submit" name="mySubmit" value="转正">
    </form> -->

    <!--dom结构部分 上传-->
    <div id="uploader-demo">
        <!--用来存放item-->
        <div id="fileList" class="uploader-list"></div>
        <div id="filePicker">选择图片</div>
        <button id="ctlBtn" class="btn btn-default">开始上传</button>  
    </div>


</body>
<script src="http://cdn.bootcss.com/jquery/1.12.3/jquery.min.js"></script>
<script type="text/javascript">
	/*$(document).ready(function(){
		var post_url = "<?php echo U('activity/get_product_class');?>";
        $.ajax({
	        type: "POST",
	        url: post_url,
	        //contentType: "application/json",
	        data:{  
		       
  			},
	        //datatype: "json",
	        success: function (data){
	        	var data = data.list;
	            //console.log(data);
	            var json = toTree(data);
	            //console.log(json)
	            var html = createCateHtml(json, data);
	            //console.log(html)
	            $(".categoryLevel").append('<ul>' + html + '</ul>');
	            
	        },
	        error: function (obj, msg, info) {
	            console.log(info, obj.responseText);
	        }
	    });
    });*/

	//是否有子级
    function hasChild(arr, id) {
        for (var i = 0, lenI = arr.length; i < lenI; i++) {
            if (arr[i].parent == id) {
                return true;
            }
        }
        return false;
    }

    //重新封装数据
    function toTree(data) {
        // 删除 所有 children,以防止多次调用
        $.each(data, function(i, item) {
            delete item.children;
        });

        // 将数据存储为 以 id 为 KEY 的 map 索引数据列
        var map = {};
        $.each(data, function(i, item) {
            map[item.id] = item;
        });

        var val = [];
        $.each(data, function(i, item) {

            // 以当前遍历项，的pid,去map对象中找到索引的id
            var parent = map[item.parent];

            // 好绕啊，如果找到索引，那么说明此项不在顶级当中,那么需要把此项添加到，他对应的父级中
            if (parent) {

                (parent.children || (parent.children = [])).push(item);

            } else {
                //如果没有在map中找到对应的索引ID,那么直接把 当前的item添加到 val结果集中，作为顶级
                val.push(item);
            }
        });

        return val;
    }

    //创建Html
    function createCateHtml(arr,data) {
        var html = "";
        for (var i = 0; i < arr.length; i++) {
            if (arr[i].children) {
                html += '<li>' + (hasChild(data, arr[i].id) ? '<span class="spread">+</span>' : "") + '<div class="item"><span>' + arr[i].name + '<input type=checkbox name=product_class[]"' +' value="' + arr[i].id + '" data-src="' + getAllParent(data,arr[i].id) + '"></div><ul style="display:none">';
                html += createCateHtml(arr[i].children, data);
            } else {
                html += '<li>' + (hasChild(data, arr[i].id) ? '<span class="spread">+</span>' : "") + '<div  class="item"><span>' + arr[i].name + '<input type=checkbox name="product_list[]"' +' value="' + getAllParent(data,arr[i].id) + '" data-src="' + arr[i].id + '"></div>';
            }
            html += "</li>";
        }
        html += "</ul>";
        return html;
    }

    function getAllParent(arr, pid) {
        var html = "";
        for (var i = 0, lenI = arr.length; i < lenI; i++) {
            if (arr[i].id === pid) {
                html += (arr[i].id + "_");
                if (arr[i].parent) {
                    html += getAllParent(arr, arr[i].parent);
                }
            }
        }
        return html;
    }


	$(".categoryLevel").on("click", ".spread", function(ev) 
	{
        var _this = $(this);
        var oParent = _this.parent();
        var aSpread = oParent.find(".spread");

        if (oParent.children("ul").size()) 
        {
            if (!_this.data("show")) 
            {
                _this.data("show", true);
                oParent.children("ul").show();
                _this.text("-");
            } 
            else 
            {
                _this.data("show", false);
                oParent.children("ul").hide();
                _this.text("+");
                aSpread.each(function() {
                    var _this = $(this);
                    var oParent = _this.parent();
                    _this.data("show", false);
                    oParent.children("ul").hide();
                    _this.text("+");
                });
            }
        }
        return false;
    });
    $(".categoryLevel").on("click", ".item input", function() {
    	var _this = $(this);
    	var oItem = _this.parents(".item").eq(0).parent();
    	var oParent = _this.parents("ul").eq(0).siblings(".item").find("input");

    	//选中所有后代
    	if (_this.prop("checked")) {
    		oItem.find("input").prop("checked", true);
    		oParent.prop("checked", true);

    	}
    	else {
    		oItem.find("input").prop("checked", false);
    	}

    });
</script>

<!--引入JS-->
<script type="text/javascript" src="/mythinkphp/public/js/webuploader/webuploader.js"></script>
<script type="text/javascript">
$(function(){  
   /*init webuploader*/  
   var $list=$("#fileList");   //这几个初始化全局的百度文档上没说明，好蛋疼。  
   var $btn =$("#ctlBtn");   //开始上传  
   var thumbnailWidth = 100;   //缩略图高度和宽度 （单位是像素），当宽高度是0~1的时候，是按照百分比计算，具体可以看api文档  
   var thumbnailHeight = 100;  
  
   var uploader = WebUploader.create({  
       // 选完文件后，是否自动上传。  
       auto: false,  
  
       // swf文件路径  
       swf: '/mythinkphp/public/js/webupload/Uploader.swf',  
  
       // 文件接收服务端。  
      server: "<?php echo U('activity/get_uploader_data');?>",
  
       // 选择文件的按钮。可选。  
       // 内部根据当前运行是创建，可能是input元素，也可能是flash.  
       pick: '#filePicker',  
  
       // 只允许选择图片文件。  
       accept: {  
            title: 'Images',  
            extensions: 'jpg,jpeg,png',
            //mimeTypes: 'image/*'
            mimeTypes: 'image/jpg,image/jpeg,image/png'
       },  
       method:'POST',  
        //fileNumLimit: 2, //限制上传个数
        fileSingleSizeLimit: 2048000, //限制单个上传图片的大小2M 
        duplicate :true  
        
   });  
   // 当有文件添加进来的时候  
   uploader.on( 'fileQueued', function( file ) {  // webuploader事件.当选择文件后，文件被加载到文件队列中，触发该事件。等效于 uploader.onFileueued = function(file){...} ，类似js的事件定义。  
       var $li = $(  
               '<div id="' + file.id + '" class="file-item thumbnail">' +  
                   '<img>' +  
                   '<div class="info">' + file.name + '</div>' +  
               '</div>'  
               ),  
           $img = $li.find('img');  
  
  
       // $list为容器jQuery实例  
       //$list.append( $li );  
       $list.html( $li );  
  
       // 创建缩略图  
       // 如果为非图片文件，可以不用调用此方法。  
       // thumbnailWidth x thumbnailHeight 为 100 x 100  
       uploader.makeThumb( file, function( error, src ) {   //webuploader方法  
           if ( error ) {  
               $img.replaceWith('<span>不能预览</span>');  
               return;  
           }  
  
           $img.attr( 'src', src );  
       }, thumbnailWidth, thumbnailHeight );  
   });  
   // 文件上传过程中创建进度条实时显示。  
   uploader.on( 'uploadProgress', function( file, percentage ) {  
       var $li = $( '#'+file.id ),  
           $percent = $li.find('.progress span');  
  
       // 避免重复创建  
       if ( !$percent.length ) {  
           $percent = $('<p class="progress"><span></span></p>')  
                   .appendTo( $li )  
                   .find('span');  
       }  
  
       $percent.css( 'width', percentage * 100 + '%' );  
   });  
  
   // 文件上传成功，给item添加成功class, 用样式标记上传成功。  
   uploader.on( 'uploadSuccess', function( file ) {  
       $( '#'+file.id ).addClass('upload-state-done');  
   });  
  
   // 文件上传失败，显示上传出错。  
   uploader.on( 'uploadError', function( file ) {  
       var $li = $( '#'+file.id ),  
           $error = $li.find('div.error');  
  
       // 避免重复创建  
       if ( !$error.length ) {  
           $error = $('<div class="error"></div>').appendTo( $li );  
       }  
  
       $error.text('上传失败');  
   });  
  
   // 完成上传完了，成功或者失败，先删除进度条。  
   uploader.on( 'uploadComplete', function( file ) {  
       $( '#'+file.id ).find('.progress').remove();  
   });  
      $btn.on( 'click', function() {  
        console.log("上传...");  
        uploader.upload();  
        console.log("上传成功");  
      });  
  });                            
    
</script>
</html>