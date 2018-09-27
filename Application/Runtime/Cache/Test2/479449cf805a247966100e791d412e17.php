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
</head>
<body>
	    *<?php echo ($data["name"]); ?>* 
		<?php if(is_array($data['list'])): $i = 0; $__LIST__ = $data['list'];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$sub): $mod = ($i % 2 );++$i;?>-<?php echo ($sub[1]); ?>-<?php endforeach; endif; else: echo "" ;endif; ?>
	    <div>
	    	<form action="<?php echo U('activity/add_activity_goods_list');?>" method="post">
	    		<select name="product_type_id" id="product_type_id">
                    <option value="0" selected="selected">请选择</option>
                    <?php if(is_array($product_class_list)): $i = 0; $__LIST__ = $product_class_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$val): $mod = ($i % 2 );++$i;?><option value="<?php echo ($val["id"]); ?>" <?php if(($val['id']) == $_REQUEST['product_class_list']): ?>selected="selected"<?php endif; ?> ><?php echo ($val["name"]); ?></option><?php endforeach; endif; else: echo "" ;endif; ?>
				</select>
	    		<!-- <input type="hidden" name="product_type_id" value="65" /> -->
		    	<!-- <?php if(is_array($product_list)): $i = 0; $__LIST__ = $product_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?>-->
	    		<div id='check_product_list'>
	    			<!-- <input type="checkbox" name="product_lists[]" value="<?php echo ($vo["id"]); ?>" /><?php echo ($vo["name"]); ?>&nbsp; -->
	    		</div>
				<!--<?php endforeach; endif; else: echo "" ;endif; ?> -->
			  <input type="submit" value="Submit" />
			</form>
	    </div>
	    <br/>
	    <div class="categoryLevel">
 			
	    </div>
</body>
<script src="http://cdn.bootcss.com/jquery/1.12.3/jquery.min.js"></script>
<script type="text/javascript">
	$('#product_type_id').change(function(){
		var _type_id = $('#product_type_id').val();
		//console.log(_type_id);
		var post_url = "<?php echo U('activity/get_product_by_id2');?>";
		//console.log(post_url);
		/*var data = {company_id:post_url};*/
		$.ajax({
	        type: "POST",
	        url: post_url,
	        //contentType: "application/json",
	        data:{  
		       type_id : _type_id,  
  			},
	        //datatype: "json",
	        success: function (data){
	            //console.log(data);
	            if(data.status){
	            	var strhtml="";
	            	var list = data.list;	
  					$.each(list, function(key, val) {  
 						//console.log(val.id);
 						//console.log(val.name);
 						strhtml += "<input type='checkbox' name='product_lists[]' value='";
 						strhtml += val.id;
 						strhtml += "'/>";
 						strhtml += val.name+"&nbsp";
					});
					//console.log(strhtml);
       
	            	$('#check_product_list').html(strhtml);
	            }
	        },
	        error: function (obj, msg, info) {
	            console.log(info, obj.responseText);
	        }
	    });
	})
</script>
<script type="text/javascript">
	$(document).ready(function(){
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
    });

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
                html += '<li>' + (hasChild(data, arr[i].id) ? '<span class="spread">+</span>' : "") + '<div class="item"><span>' + arr[i].name + '<input type=checkbox name=produst_class[]' +' value="' + arr[i].id + '"></div><ul style="display:none">';
                html += createCateHtml(arr[i].children, data);
            } else {
                html += '<li>' + (hasChild(data, arr[i].id) ? '<span class="spread">+</span>' : "") + '<div  class="item"><span>' + arr[i].name + '<input type=checkbox name="produst_list[]' +' value="' + arr[i].id + '"></div>';
            }
            html += "</li>";
        }
        html += "</ul>";
        return html;
    }

	$(".categoryLevel").on("click", ".spread", function(ev) 
	{
		console.log(1);
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
</html>