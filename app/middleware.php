<?php

//use app\middleware\AllowCrossDomainMiddleware;

// 全局中间件定义文件
return [
    // 全局请求缓存
    // \think\middleware\CheckRequestCache::class,
    // 多语言加载
    // \think\middleware\LoadLangPack::class,
    // Session初始化
    \think\middleware\SessionInit::class,
    // 跨域
//     \think\middleware\AllowCrossDomain::class,
    // 自定义跨域
    \app\middleware\AllowCrossDomain::class,
    // 验证登录状态
    // \app\middleware\CheckToken::class

];
