<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>奖励概率</title>
	<style type="text/css">
		th{width: 65px;}
	</style>
</head>
<body>
	<br/>
	<table border="2">
		<tr>
		    <!-- <th>位数</th> -->
		    <th>0</th>
		    <th>1</th>
		    <th>2</th>
		    <th>3</th>
		    <th>4</th>
		    <th>5</th>
		    <th>6</th>
		    <th>7</th>
		    <th>8</th>
		    <th>9</th>
		    <!-- <th>号码</th> -->
	  	</tr>
	  	<?php if(is_array($list)): $k = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k; if($k < 5 ): ?><tr>
		  		<?php if(is_array($vo)): $i = 0; $__LIST__ = $vo;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vos): $mod = ($i % 2 );++$i;?><td><!-- <?php echo ($vos["number"]); ?> --> <?php echo ($vos["c"]); ?>次 <?php echo ($vos["p_lv"]); ?>% <?php if($vos["cha"] < 0 ): ?><span style="color: red"><?php echo ($vos["cha"]); ?>%↓</span><?php else: ?><span style="color: green"><?php echo ($vos["cha"]); ?>%↑</span><?php endif; ?></td><?php endforeach; endif; else: echo "" ;endif; ?>
				</tr><?php endif; endforeach; endif; else: echo "" ;endif; ?>
	</table>
	<table border="2">
	  	<?php if(is_array($c_list)): $k = 0; $__LIST__ = $c_list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($k % 2 );++$k;?><tr>
				<td><?php echo ($vo["opencode"]); ?></td>
			</tr><?php endforeach; endif; else: echo "" ;endif; ?>
	</table>
	<div>
		&nbsp;&nbsp;&nbsp;&nbsp;总:<?php echo ($total); ?>
	</div>
	<br/>
	<!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    <div id="main" style="height:400px"></div>
    <!-- 为ECharts准备一个具备大小（宽高）的Dom -->
    <div id="main2" style="height:400px"></div>
</body>
<script src="http://cdn.bootcss.com/jquery/1.12.3/jquery.min.js"></script>
<script src="http://echarts.baidu.com/build/dist/echarts.js"></script>
    <script type="text/javascript">
    var post_url = "<?php echo U('matters/cai_piao_by_ajax');?>";
    	$.ajax({
	        type: "POST",
	        url: post_url,
	        //contentType: "application/json",
	        data:{  
		        
  			},
	        //datatype: "json",
	        success: function (data){
	            //console.log(data);
	            if(data.status)
	            {
	            	// 路径配置
        require.config({
            paths: {
                echarts: 'http://echarts.baidu.com/build/dist'
            }
        });
        
        // 使用
        require(
            [
                'echarts',
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/line', 
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById('main')); 
                
                var option = {
			    title : {
			        text: '每位数字概率',
			    },
			    tooltip : {
			        trigger: 'axis'
			    },
			    legend: {
			        data:['第1位数','第2位数','第3位数','第4位数','第5位数','第6位数','第7位数',]
			    },
			    toolbox: {
			        show : true,
			        feature : {
			            mark : {show: true},
			            dataView : {show: true, readOnly: false},
			            magicType : {show: true, type: ['line', 'bar']},
			            restore : {show: true},
			            saveAsImage : {show: true}
			        }
			    },
			    calculable : true,
			    xAxis : [
			        {
			            type : 'category',
			            boundaryGap : false,
			            data : ['开0','开1','开2','开3','开4','开5','开6','开7','开8','开9']
			        }
			    ],
			    yAxis : [
			        {
			            type : 'value',
			            axisLabel : {
			                formatter: '{value}次'
			            }
			        }
			    ],
			    series : [
			        {
			            name:'第1位数',
			            type:'line',
			            data:data['data'][1],
		                markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name: '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第2位数',
			            type:'line',
			            data:data['data'][2],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第3位数',
			            type:'line',
			            data:data['data'][3],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第4位数',
			            type:'line',
			            data:data['data'][4],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第5位数',
			            type:'line',
			            data:data['data'][5],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第6位数',
			            type:'line',
			            data:data['data'][6],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        },
			        {
			            name:'第7位数',
			            type:'line',
			            data:data['data'][7],
			            markPoint : {
			                data : [
			                    {type : 'max', name: '出现最多次数'},
			                    {type : 'min', name: '出现最少次数'}
			                ]
            			},
			            markLine : {
			                data : [
			                    {type : 'average', name : '平均值'}
			                ]
			            }
			        }
			    ]
			};
                    
        
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );
	            }
	        },
	        error: function (obj, msg, info) {
	            console.log(info, obj.responseText);
	        }
	    });
        
    </script>
<script type="text/javascript">
	var post_url = "<?php echo U('matters/get_data_zishu');?>";
	//console.log(post_url);
	/*var data = {company_id:post_url};*/
	$.ajax({
        type: "POST",
        url: post_url,
        //contentType: "application/json",
        data:{  
	        
			},
        //datatype: "json",
        success: function (data){
            console.log(data);
            if(data.status)
            {
            	
            	// 路径配置
        require.config({
            paths: {
                echarts: 'http://echarts.baidu.com/build/dist'
            }
        });
        
        // 使用
        require(
            [
                'echarts',
                'echarts/chart/bar', // 使用柱状图就加载bar模块，按需加载
                'echarts/chart/line', 
            ],
            function (ec) {
                // 基于准备好的dom，初始化echarts图表
                var myChart = ec.init(document.getElementById('main2')); 
                
                var option = {
			    title : {
			        text: '每位质合概率(最近20期)',
			        //subtext: '1质数'+data['zh_c']['z']+'  -1合数'+data['zh_c']['h']
			        subtext: '1质数 -1合数'
			    },
			    tooltip : {
			        trigger: 'axis'
			    },
			    legend: {
			        data:['第1位数','第2位数','第3位数','第4位数','第5位数','第6位数','第7位数',]
			    },
			    toolbox: {
			        show : true,
			        feature : {
			            mark : {show: true},
			            dataView : {show: true, readOnly: false},
			            magicType : {show: true, type: ['line', 'bar']},
			            restore : {show: true},
			            saveAsImage : {show: true}
			        }
			    },
			    calculable : true,
			    xAxis : [
			        {
			            type : 'category',
			            boundaryGap : false,
			            data : data['c_list']
			        }
			    ],
			    yAxis : [
			        {
			            type : 'value',
			            axisLabel : {
			                formatter: '{value}'
			            }
			        }
			    ],
			    series : [
			        {
			            name:'第1位数',
			            type:'line',
			            data:data['data'][1],
		          
			          
			        },
			        {
			            name:'第2位数',
			            type:'line',
			            data:data['data'][2],
			            
			        },
			        {
			            name:'第3位数',
			            type:'line',
			            data:data['data'][3],
			            
			        },
			        {
			            name:'第4位数',
			            type:'line',
			            data:data['data'][4],
			            
			        },
			        {
			            name:'第5位数',
			            type:'line',
			            data:data['data'][5],
			            
			          
			        },
			        {
			            name:'第6位数',
			            type:'line',
			            data:data['data'][6],
			            
			           
			        },
			        {
			            name:'第7位数',
			            type:'line',
			            data:data['data'][7],
			            
			         
			        }
			    ]
			};
                    
                // 为echarts对象加载数据 
                myChart.setOption(option); 
            }
        );

            }
        },
        error: function (obj, msg, info) {
            console.log(info, obj.responseText);
        }
    });
</script>
</html>