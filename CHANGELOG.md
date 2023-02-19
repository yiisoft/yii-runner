# Yii Runner Change Log

## 2.0.0 under development

- Сhg #37: Remove parameters in `ApplicationRunner` methods `runBootstrap()` and `checkEvents()`, instead are used 
  internal container and config instances (@vjik) 
- Chg #39: Adapt to Yii configuration groups names convention (@vjik)
- Chg #39: Remove methods `withBootstrap()`, `withoutBootstrap()`, `withCheckingEvents()`, `withoutCheckingEvents()` 
  from `ApplicationRunner` (@vjik) 
- New #38, #39: Add ability to configure all config group names (@vjik)
- New #39: Add parameter `$checkEvents` to `ApplicationRunner` constructor (@vjik)
- Chg #39: Remove `ConfigFactory`, instead it move code to `ApplicationRunner::createDefaultConfig()` method (@vjik)
- Enh #39: Make methods `ApplicationRunner::getConfig()` and `ApplicationRunner::getContainer()` public (@vjik)

## 1.2.1 November 07, 2022

- Enh #26: Add support for `yiisoft/definitions` version `^3.0` (@vjik)

## 1.2.0 July 29, 2022

- Chg #21: Store config inside DI container (@xepozz)

## 1.1.2 June 27, 2022

- Enh #19: Add support for `psr/container` version `^2.0` (@vjik)

## 1.1.1 June 17, 2022

- Enh #15: Add support for `yiisoft/definitions` version `^2.0` (@vjik)

## 1.1.0 April 18, 2022

- New #10: Add container's tags support (@xepozz)

## 1.0.0 January 17, 2022

- Initial release.
