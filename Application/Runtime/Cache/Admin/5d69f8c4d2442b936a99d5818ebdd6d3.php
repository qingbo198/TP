<?php if (!defined('THINK_PATH')) exit();?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
    <head>
        <title>用户列表</title>
        <link href="/TP/Public/Css/style.css" rel="stylesheet" type="text/css" />
        <link href="/TP/Public/layui/css/layui.css" rel="stylesheet" type="text/css" />
        <script type="text/javascript" src="/TP/Public/Js/jquery.min.js"></script>
        <script type="text/javascript" src="/TP/Public/layer/layer.js"></script>
        <script type="text/javascript" src="/TP/Public/layui/layui.js"></script>
        <script type="text/javascript" src="/TP/Public/laydate/laydate.dev.js"></script>
        <script type="text/javascript" src="/TP/Public/Js/userIndex/index.js"></script>

    </head>

    <body>
        <a href="javascript:history.go(-1);">返回</a>
        <div class="out">
            <div class="div_search">
                <form action="<?php echo U('User/index');?>" method="post">
                    <input type="text" name="search" value="<?php echo ($keywords); ?>"/>
                    <input type="submit" name="submit" value="搜索" />
                    <input type="text" name="date" value="" id="date" /><button>日期</button>
                </form>
            </div>
            <div class="table_div">
            <form>
                <table class="tb">
                    <tr>
                        <th>ID</th>
                        <th>用户名</th>
                        <th>地址</th>
                        <th>手机</th>
                        <th>最后操作时间</th>
                        <th>操作</th>
                    </tr>
                    <?php if(is_array($list)): foreach($list as $key=>$vo): ?><tr>
                            <td><?php echo ($vo["id"]); ?></td>
                            <td><?php echo ($vo["username"]); ?></td>
                            <td><?php echo ($vo["addr"]); ?></td>
                            <td><?php echo ($vo["phone"]); ?></td>
                            <td><?php echo ($vo["lasttime"]); ?></td>
                            <td>
                                <a href="<?php echo U('Admin/User/add');?>" class="button_add">新增</a>
                                <a href="<?php echo U('Admin/User/edit?id=');?>?id=<?php echo ($vo["id"]); ?>" class="button_edit">修改</a>
                                <a href="<?php echo U('Admin/User/show');?>"class="button_show">详情</a>
                                <a class="button_del" style="cursor: pointer" data-id="<?php echo ($vo["id"]); ?>">删除</a>
                            </td>
                        </tr><?php endforeach; endif; ?>
                    <tr class="content">
                        <!--<td colspan="3" bgcolor="#FFFFFF">&nbsp;<?php echo ($page); ?></td>-->
                        <td colspan=6 bgcolor="#FFFFFF">
                            <div class="pages">
                                <?php echo ($page); ?>
                            </div>
                        </td>  
                    </tr>
                </table>

            </form>
            </div>
            <div class="province">
                省份:<select name="province">
                         <option>--请选择--</option>
                    <?php if(is_array($result)): foreach($result as $key=>$vo): ?><option><?php echo ($vo["province"]); ?></option><?php endforeach; endif; ?>

                </select>
                市： <select name="city">
                        <option>--请选择--</option>
                </select>
                地区：<select name="area">
                    <option>--请选择--</option>
                </select>
            </div>
  <!--        <form class="layui-form" action="">
                <div class="layui-form-item">
                    <label class="layui-form-label">输入框</label>
                    <div class="layui-input-block">
                        <input type="text" name="title" required  lay-verify="required" placeholder="请输入标题" autocomplete="off" class="layui-input">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">密码框</label>
                    <div class="layui-input-inline">
                        <input type="password" name="password" required lay-verify="required" placeholder="请输入密码" autocomplete="off" class="layui-input">
                    </div>
                    <div class="layui-form-mid layui-word-aux">辅助文字</div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">选择框</label>
                    <div class="layui-input-block">
                        <select name="city" lay-verify="required">
                            <option value=""></option>
                            <option value="0">北京</option>
                            <option value="1">上海</option>
                            <option value="2">广州</option>
                            <option value="3">深圳</option>
                            <option value="4">杭州</option>
                        </select>
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">复选框</label>
                    <div class="layui-input-block">
                        <input type="checkbox" name="like[write]" title="写作">
                        <input type="checkbox" name="like[read]" title="阅读" checked>
                        <input type="checkbox" name="like[dai]" title="发呆">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">开关</label>
                    <div class="layui-input-block">
                        <input type="checkbox" name="switch" lay-skin="switch">
                    </div>
                </div>
                <div class="layui-form-item">
                    <label class="layui-form-label">单选框</label>
                    <div class="layui-input-block">
                        <input type="radio" name="sex" value="男" title="男">
                        <input type="radio" name="sex" value="女" title="女" checked>
                    </div>
                </div>
                <div class="layui-form-item layui-form-text">
                    <label class="layui-form-label">文本域</label>
                    <div class="layui-input-block">
                        <textarea name="desc" placeholder="请输入内容" class="layui-textarea"></textarea>
                    </div>
                </div>
                <div class="layui-form-item">
                    <div class="layui-input-block">
                        <button class="layui-btn" lay-submit lay-filter="formDemo">立即提交</button>
                        <button type="reset" class="layui-btn layui-btn-primary">重置</button>
                    </div>
                </div>
            </form> -->

            <script>
            //Demo
                layui.use('form', function () {
                    var form = layui.form;

                    //监听提交
                    form.on('submit(formDemo)', function (data) {
                        layer.msg(JSON.stringify(data.field));
                        return false;
                    });
                });
            </script>
        </div>

    </body>
</html>