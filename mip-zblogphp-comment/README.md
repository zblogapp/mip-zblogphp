# mip-zblogphp-comment MIP Z-BlogPHP评论支持

MIP Z-BlogPHP支持组件 之 评论

标题|内容
----|----
类型|通用
支持布局|responsive, fixed-height, fixed
所需脚本|https://c.mipcdn.com/static/v1/mip-zblogphp-comment/mip-zblogphp-comment.js

## 使用方法

以下为在Z-BlogPHP模板内使用方法：
1. 启用MIP支持插件
2. 在主题的``header.php``内，插入
```html
<meta name="bloghost" content="{$host}">
```
3. 将主题内的``{template:comments}``换成
```html
<mip-zblogphp-comment post-id="{$article.ID}"></mip-zblogphp-comment>
```
4. 在主题的``footer.php``内插入
```html
<script src="https://c.mipcdn.com/static/v1/mip-zblogphp-comment/mip-zblogphp-comment.js"></script>
```
5. （可选）在主题内新建``mip-comment.php``，参照example进行修改。

### 示例
```html
<mip-zblogphp-comment post-id="{$article.ID}"></mip-zblogphp-comment>
```

## 属性

### post-id

说明：用于提交的文章ID
必选项：是
类型：字符串
取值范围：数值
单位：无
默认值：''

### height

说明：高度，设置为`auto`时，将自动获取评论框高度。
必选项：否
类型：`auto` | 数字
单位：无
默认值：`auto`

### 其它
[mip-iframe](https://www.mipengine.org/examples/mip/mip-iframe.html)组件可用的属性，除``src``、``srcdoc``与``height``外，其他均支持。
