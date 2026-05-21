# Fluence Shipping Restriction Pro

[![WordPress Plugin Version](https://img.shields.io/wordpress/plugin/v/fluence-shipping-restriction-pro)](https://wordpress.org/plugins/fluence-shipping-restriction-pro/)
[![WooCommerce Compatible](https://img.shields.io/badge/WooCommerce-6.0+-96588a)](https://woocommerce.com/)
[![PHP Version](https://img.shields.io/badge/PHP-7.4+-777bb4)](https://php.net/)

**Advanced shipping restriction plugin for WooCommerce** – block orders based on products, categories, and countries. Track rule hits with built‑in charts.

> 高级 WooCommerce 运费限制插件 – 根据产品、分类和国家/地区阻止订单，并内置统计图表追踪规则命中次数。

---

## 📖 中文说明

### 插件简介

Fluence Shipping Restriction Pro 是一款基于 WooCommerce 的购买限制插件。它允许你创建灵活的规则，当购物车中的商品符合特定产品或分类，且用户所在国家被限制时，自动阻止结账或隐藏下单按钮，并显示自定义提示消息。

### 主要功能

- ✅ **产品/分类限制** – 每条规则可同时包含产品和分类（取并集）
- ✅ **多国家支持** – 选择任意多个国家/地区适用规则
- ✅ **两种限制模式**  
  - `Block Checkout`：阻止结账，显示错误并禁止提交订单  
  - `Hide Order Button`：隐藏“下单”按钮，用户无法提交
- ✅ **自定义消息** – 支持动态变量 `{{product_name}}` 和 `{{country}}`
- ✅ **统计图表** – 以柱状图展示每条规则被触发的次数
- ✅ **Ajax 产品搜索** – 通过 Select2 实现高效产品选择
- ✅ **轻量高效** – 除 WooCommerce 外无任何外部依赖

### 环境要求

- WordPress 5.6+
- WooCommerce 6.0+
- PHP 7.4+（推荐 PHP 8.0+）

### 安装方法

1. 将 `fluence-shipping-restriction-pro` 文件夹上传至 `/wp-content/plugins/`
2. 在 WordPress 后台「插件」页面激活插件
3. 确保 **WooCommerce 已激活**（插件依赖 WooCommerce）
4. 进入左侧菜单 **Shipping Restriction**（盾牌图标）开始使用

### 使用指南

#### 创建限制规则

1. **Rule Name** – 输入规则名称（如“美国 - 高端产品禁运”）
2. **Products** – 搜索并选择要限制的商品（可多选）
3. **Categories** – 选择要限制的商品分类（可多选）
4. **Countries** – 选择适用此规则的国家（可多选，ISO 代码如 US、GB）
5. **Mode** – 选择 `Block Checkout` 或 `Hide Order Button`
6. **Message** – 自定义提示消息，可使用 `{{product_name}}` 和 `{{country}}`
7. 点击 **Save Rule** – 保存后页面自动刷新，新规则出现在下方列表中

#### 统计图表

- 页面顶部展示柱状图，显示每条规则的 `Hits`（命中次数）
- 每次用户在前台触发规则（被阻止或按钮隐藏），命中次数自动 +1

#### 管理已有规则

- 查看规则列表（国家超过3个会折叠显示）
- 删除/编辑规则：当前版本未提供界面操作，可通过删除 WordPress 选项 `fsrp_rules_v2` 实现（设置 `[]`）

### 匹配逻辑

1. 遍历购物车中每个商品：
   - 获取商品 ID（变体商品取父级 ID）
   - 获取商品所属的所有分类 ID
2. 遍历所有规则：
   - 用户的国家必须在规则的 `countries` 列表中
   - 商品的 ID 在规则的 `products` 列表中 **或者** 商品的分类与规则的 `categories` 有交集
3. 一旦匹配命中，立即触发限制（不再继续检测）

**国家检测顺序：**  
收货国家 → 账单国家 → WooCommerce 默认商店国家

### 常见问题

**问：后台菜单不显示？**  
答：确保 WooCommerce 已激活，PHP 版本 ≥7.4，并开启 `WP_DEBUG` 查看是否有致命错误。

**问：规则为什么不生效？**  
答：检查用户国家是否与规则中设置的国家完全一致（ISO 代码，如 `US` 而非 `USA`）；确认购物车中包含所选产品或分类；尝试清除 WooCommerce 会话（退出登录后重新登录）。

**问：如何删除所有规则？**  
答：通过 WP CLI 执行 `wp option delete fsrp_rules_v2`，或在 `wp-admin/options.php` 中将 `fsrp_rules_v2` 的值改为 `[]`。

**问：是否支持多语言？**  
答：支持。所有字符串均可翻译，插件包含 `.pot` 文件。

### 开发者说明

- **存储选项名称：** `fsrp_rules_v2`（序列化数组）
- **规则数组结构：**
  ```php
  [
      'name'       => 'string',
      'products'   => [int, ...],
      'categories' => [int, ...],
      'countries'  => [string, ...],
      'mode'       => 'block' | 'hide',
      'message'    => 'string',
      'hits'       => int
  ]

  核心钩子：

woocommerce_checkout_process – 执行阻止逻辑

woocommerce_review_order_before_submit – 隐藏下单按钮

woocommerce_package_rates – 可修改运费标签（示例已提供）

更新日志
1.2.1 (2026-05-21)

改进 UI 样式及响应式布局

修复 Chart.js 加载问题（CDN 回退）

增加 WooCommerce 依赖检查，避免致命错误

优化已有规则表格中国家列表的显示

1.2.0

首次公开发布

许可证
GPL v2 或更高版本

作者
kim

🇬🇧 English Documentation
Description
Fluence Shipping Restriction Pro is a powerful WooCommerce plugin that lets you restrict orders based on products, categories, and countries. It supports two restriction modes, custom messages with placeholders, and includes a statistics chart to track rule hits.

Features
Product & category restrictions – union logic within a single rule

Multi‑country blocking – select any number of countries per rule

Two restriction modes

Block Checkout – show error and prevent order submission

Hide Order Button – visually hide the “Place order” button

Custom messages with placeholders {{product_name}} and {{country}}

Statistics dashboard – bar chart of rule hit counts

Ajax product search – easy product selection via Select2

Lightweight & fast – no external dependencies except WooCommerce

Requirements
WordPress 5.6+

WooCommerce 6.0+

PHP 7.4+ (PHP 8.0+ recommended)

Installation
Upload fluence-shipping-restriction-pro to /wp-content/plugins/

Activate the plugin through the Plugins menu in WordPress

Ensure WooCommerce is active (the plugin requires it)

Go to Shipping Restriction in the admin sidebar (shield icon)

How to Use
Create a Restriction Rule
Enter a Rule Name (e.g., “USA - Premium Products”)

Products – search and select products (multiple allowed)

Categories – select product categories (multiple allowed)

Countries – select target countries (multiple allowed, ISO codes like US, GB)

Mode – choose Block Checkout or Hide Order Button

Message – write a custom message; use {{product_name}} and {{country}}

Click Save Rule – the page will reload and the rule appears in the list

Statistics
A bar chart shows how many times each rule was triggered (Hits)

Hits are incremented automatically on the frontend when a rule matches the cart and country

Manage Rules
View – existing rules are listed in a table (countries truncated after 3)

Delete/Edit – not available via UI; you can delete the option fsrp_rules_v2 from wp-admin/options.php (set value to [])

Matching Logic
Plugin checks each cart item:

Gets product ID (parent ID for variations)

Gets all category IDs of the product

For each rule:

User’s country must be in rule’s countries list

Product ID must be in rule’s products OR category IDs intersect with rule’s categories

First match triggers the restriction (no further checks)

Country detection priority:
Shipping country → Billing country → WooCommerce base country

FAQ
Q: The plugin menu does not appear in admin.
A: Ensure WooCommerce is active, PHP version ≥7.4, and enable WP_DEBUG to see any fatal errors.

Q: My rule doesn’t work.
A: Verify the user’s country matches exactly (ISO code, e.g. US, not USA); check if the cart contains the selected products or categories; clear WooCommerce session (log out and back in).

Q: How to remove all rules at once?
A: Run wp option delete fsrp_rules_v2 via WP CLI, or set the value to [] in wp-admin/options.php.

Q: Is the plugin translation ready?
A: Yes, all strings are translatable. A .pot file is included.

Developer Notes
Option name: fsrp_rules_v2 (serialized array)

Rule structure:

php
[
    'name'       => 'string',
    'products'   => [int, ...],
    'categories' => [int, ...],
    'countries'  => [string, ...],
    'mode'       => 'block' | 'hide',
    'message'    => 'string',
    'hits'       => int
]
Core hooks:

woocommerce_checkout_process – runs blocking logic

woocommerce_review_order_before_submit – hides button

woocommerce_package_rates – can modify shipping labels (example included)

Changelog
1.2.1 (2026-05-21)

Improved UI styles and responsive layout

Fixed Chart.js loading (CDN fallback)

Added WooCommerce dependency check to prevent fatal errors

Optimized country list display in existing rules table

1.2.0

Initial public release

License
GPL v2 or later

Author
kim

🤝 Contributing
Pull requests and issues are welcome. Please follow WordPress coding standards.

📧 Support
For support, please open an issue on GitHub or contact the author via the WordPress plugin page.
