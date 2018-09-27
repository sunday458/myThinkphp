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
    <br/>
    <form action="<?php echo U('activity/test_checkbox');?>" method="post">
	    <div class="categoryLevel">
 			
	    </div>
        <input type="submit" value="Submit" />
    </form>
</body>
<script src="http://cdn.bootcss.com/jquery/1.12.3/jquery.min.js"></script>
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
</html>