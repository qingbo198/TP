<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
    <title>商品分类列表</title>
    <link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css"/>

</head>

<body>
<span>商品分类列表</span>&nbsp;&nbsp;
<a href="javascript:history.go(-1);">返回>></a>

<div style="width: 100%">
        <table class="tb">
            <tr>
                <th>ID</th>
                <th width="60%">分类名称</th>
                <th>操作</th>
            </tr>
            <?php foreach($list as $v){?>
            <tr>
                <td><?php echo $v["id"]?></td>
                <td align="left"><?php echo str_repeat("&brvbar;---",$v["lev"]).$v["name"]?></td>
                <td>
                    <a href="add?id=<?php echo $v['id'] ?>" class="button_add">新增</a>
                    <a href="edit?pid=<?php echo $v['pid']?>&id=<?php echo $v['id'] ?>" class="button_edit">修改</a>
                    <a data-id="<?php echo $v['id'] ?>" class="button_del" style="cursor: pointer;">删除</a>
                </td>
            </tr>
            <?php }?>
            <!--<tr class="content">-->
                <!--&lt;!&ndash;<td colspan="3" bgcolor="#FFFFFF">&nbsp;<?php echo ($page); ?></td>&ndash;&gt;-->
                <!--<td colspan=6 bgcolor="#FFFFFF">-->
                    <!--<div class="pages">-->
                        <!--<?php echo ($page); ?>-->
                    <!--</div>-->
                <!--</td>-->
            <!--</tr>-->
        </table>
</div>
<script src="https://cdn.bootcss.com/jquery/3.3.1/jquery.min.js"></script>
<script src="https://cdn.bootcss.com/layer/2.3/layer.js"></script>
<script>
    $(function(){
        $('.button_del').click(function(){
            var id = $(this).attr('data-id');//获取自定义属性值
            layer.msg('确定要删除此分类吗？',{
                time:0,//不自动关闭，
                btn:['确定','取消'],
                yes:function(){
                    $.ajax({
                        url:'del',
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