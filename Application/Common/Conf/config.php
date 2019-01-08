<?php

		return array(
				//'配置项'=>'配置值'

				/* 自定义外部引用 */
				'TMPL_PARSE_STRING' => array(
						'__Layer__' => __ROOT__ . '/Public/layer',
						'__Layui__' => __ROOT__ . '/Public/layui',
						'__Laydate__' => __ROOT__ . '/Public/laydate',
						'__JS__' => __ROOT__ . '/Public/Js',
						'__CSS__' => __ROOT__ . '/Public/Css',
						'__IMAGE__' => __ROOT__ . '/Public/Image',
						'__DATA__' => __ROOT__ . '/Data/',
						'__Home__' => __ROOT__ . '/Home',
                        '__Public__'=> __ROOT__ . '/Public'
						
				),
				/* 项目分组设置 */
				'APP_GROUP_LIST' => 'Home,Admin', //项目分组设定
				'DEFAULT_GROUP' => 'Home', //默认分组
		);
		