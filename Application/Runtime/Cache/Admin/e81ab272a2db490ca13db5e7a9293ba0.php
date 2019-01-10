<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>商品列表</title>
</head>
<style>
    .list_tab{width:90%;border:solid #ccc 1px;margin:5px auto;border-collapse: collapse;text-align: center;}
    .list_tab th{border:solid #ccc 1px;}
    .list_tab td{border:solid #ccc 1px;}
    a{text-decoration: none;cursor: pointer;color:black;}
    .add_pro{margin-left: 84px;border: solid #707070 1px;border-radius: 3px;}
    .pages a,.pages span {
        display:inline-block;
        padding:2px 5px;
        margin:0 1px;
        border:1px solid #f0f0f0;
        -webkit-border-radius:3px;
        -moz-border-radius:3px;
        border-radius:3px;
    }
    .pages a,.pages li {
        display:inline-block;
        list-style: none;
        text-decoration:none; color:#58A0D3;
    }
    .pages a.first,.pages a.prev,.pages a.next,.pages a.end{
        margin:0;
    }
    .pages a:hover{
        border-color:#50A8E6;
    }
    .pages span.current{
        background:#50A8E6;
        color:#FFF;
        font-weight:700;
        border-color:#50A8E6;
    }
    .search_p{float: right;margin-right: 86px;}
</style>
<body>
<div class="list">
    <a class="add_pro" href="<?php echo U('Product/add');?>">新增商品</a>
    <form action="<?php echo U('Product/index');?>" method="get">
    <p class="search_p">商品名称：<input type="text" name="name" value="" />&nbsp<button>查询</button></p>
    </form>
    <table class="list_tab">
        <tr>
            <th>ID</th>
            <th>排序</th>
            <th>商品名称</th>
            <th>所属分类</th>
            <th>价格</th>
            <th>商品图片</th>
            <th>库存</th>
            <th class="sort" data-id="add_time" style="cursor: pointer;">添加时间</th>
            <th>更新时间</th>
            <th>状态</th>
            <th>操作</th>
        </tr>
        <?php if(is_array($list)): $i = 0; $__LIST__ = $list;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
                <td><?php echo ($vo["id"]); ?></td>
                <td><?php echo ($vo["sort"]); ?></td>
                <td><?php echo ($vo["name"]); ?></td>
                <td><?php echo ($vo["type"]); ?></td>
                <td><?php echo ($vo["price"]); ?></td>
                <td>
                    <?php if(is_array($vo["img"])): $i = 0; $__LIST__ = $vo["img"];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$v): $mod = ($i % 2 );++$i;?><img src="<?php echo ($v); ?>" width="70" onclick="showImg(this.src)" /><?php endforeach; endif; else: echo "" ;endif; ?>
                </td>
                <td><?php echo ($vo["stock"]); ?></td>
                <td><?php echo ($vo["add_time"]); ?></td>
                <td><?php echo ($vo["last_time"]); ?></td>
                <?php if($vo["status"] == 1): ?><td>显示</td>
                <?php elseif($vo["status"] == 0): ?>
                    <td>隐藏</td><?php endif; ?>

                <td>
                    <a class="edit" href="<?php echo U('Product/edit');?>?id=<?php echo ($vo["id"]); ?>&pid=<?php echo ($vo["pid"]); ?>">编辑</a>
                    <a class="delete" data-id="<?php echo ($vo["id"]); ?>">删除</a>
                </td>

            </tr><?php endforeach; endif; else: echo "" ;endif; ?>
        <tr class="content">
            <!--<td colspan="3" bgcolor="#FFFFFF">&nbsp;<?php echo ($page); ?></td>-->
            <td colspan="11" bgcolor="#FFFFFF">
                <div class="pages">
                <?php echo ($page); ?>
                </div>
            </td>
        </tr>
    </table>
</div>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
    //排序
    $('.sort').click(function(){
        var field = $(this).attr('data-id');//获取自定义属性值
        //layer.alert(field);return;
        $.ajax({
            url:'index',
            type:'get',
            data:{field:field},
            dataType:'json',
            success:function (data) {
                window.location.reload();
            },
            error:function () {
                window.location.reload();
            }
        })
    })



//点击查看大图
    function showImg(url) {
        var img = "<img src='" + url + "' />";
        layer.open({
            type: 1,
            title: false,
            closeBtn: 0,
            area: ['auto','auto'],
            skin: 'layui-layer-nobg', //没有背景色
            shadeClose: true,
            content: img
        });
    }

    $(function(){
        $('.delete').click(function(){
            var id = $(this).attr('data-id');//获取自定义属性值
            layer.msg('确定要删除改商品吗？',{
                time:0,//不自动关闭，
                btn:['确定','取消'],
                yes:function(){
                    $.ajax({
                        url:'delete',
                        type:'post',
                        data:{id:id},
                        dataType:'json',
                        success:function(data){
                            if(data.status=='1'){
                                layer.msg(data.msg,{icon:1});
                            }else if(data.status=='2'||data.status=='3'){
                                layer.msg(data.msg,{icon:2});
                            }

                        }
                    })
                }
            })

        })
    })
</script>
</body>
</html>