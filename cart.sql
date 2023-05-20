/*
 Navicat Premium Data Transfer

 Source Server         : 本地
 Source Server Type    : MySQL
 Source Server Version : 50726 (5.7.26)
 Source Host           : localhost:3306
 Source Schema         : cart

 Target Server Type    : MySQL
 Target Server Version : 50726 (5.7.26)
 File Encoding         : 65001

 Date: 24/04/2023 14:29:15
*/

SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

-- ----------------------------
-- Table structure for clue
-- ----------------------------
DROP TABLE IF EXISTS `clue`;
CREATE TABLE `clue`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `openid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户的id',
  `user_name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户名',
  `sex` int(1) NOT NULL DEFAULT 1 COMMENT '1 男 0 女',
  `phone_number` varchar(11) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '用户手机号码',
  `CartBrandID` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '汽车品牌ID',
  `provinceID` int(11) NOT NULL COMMENT '省份ID',
  `cityID` int(11) NOT NULL COMMENT '市级ID',
  `createtime` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '上传时间',
  `PhoneBelongingplace` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '号码归属地',
  `flag` int(1) NULL DEFAULT 1 COMMENT '1 有效 0 无效 2 审核中 3 伪删除',
  `Soldout` int(1) NULL DEFAULT 1 COMMENT '0 已卖完 1 还有',
  `periodofvalidity` datetime NULL DEFAULT NULL COMMENT '有效时间',
  `sales` int(11) NULL DEFAULT NULL COMMENT '售卖次数',
  `Tosell` int(11) NULL DEFAULT 0 COMMENT '以卖条数',
  `unitPrice_1` decimal(8, 0) NOT NULL DEFAULT 0 COMMENT '线索单价',
  `unitPrice_2` decimal(8, 0) NOT NULL DEFAULT 0 COMMENT '线索单价',
  `unitPrice_3` decimal(8, 0) NOT NULL DEFAULT 0 COMMENT '线索单价',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 38 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of clue
-- ----------------------------
INSERT INTO `clue` VALUES (36, 'oYa1V6tZP8ktr7RH7kE8yznCBYXU', '陈', 0, '13881754052', '27', 12, 12, '2023-04-23 18:08:40', '四川成都', 1, 1, NULL, 3, 3, 100, 80, 60);
INSERT INTO `clue` VALUES (35, 'oYa1V6tZP8ktr7RH7kE8yznCBYXU', '里', 1, '13881754059', '221', 12, 12, '2023-04-23 17:56:59', '四川成都', 1, 1, NULL, 3, 0, 1, 1, 1);
INSERT INTO `clue` VALUES (34, 'oYa1V6tZP8ktr7RH7kE8yznCBYXU', '罗', 1, '15883854381', '120', 8, 8, '2023-04-23 15:53:43', '四川-德阳', 1, 1, NULL, 2, 1, 1, 1, 0);
INSERT INTO `clue` VALUES (37, 'oYa1V6tZP8ktr7RH7kE8yznCBYXU', '罗', 1, '15883854692', '27', 10, 10, '2023-04-24 13:58:36', '四川成都', 1, 1, NULL, 2, 0, 2000, 2000, 0);

-- ----------------------------
-- Table structure for t_car_brand
-- ----------------------------
DROP TABLE IF EXISTS `t_car_brand`;
CREATE TABLE `t_car_brand`  (
  `id` int(10) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `img` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `firstletter` char(3) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 400 CHARACTER SET = utf8 COLLATE = utf8_general_ci COMMENT = '品牌' ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_car_brand
-- ----------------------------
INSERT INTO `t_car_brand` VALUES (117, 'AC Schnitzer', '//car3.autoimg.cn/cardfs/series/g27/M01/B0/62/autohomecar__ChcCQFs9vBKAO3YSAAAW0WOWvRc555.png', 'A');
INSERT INTO `t_car_brand` VALUES (276, 'ALPINA', '//car3.autoimg.cn/cardfs/series/g27/M05/AB/2E/autohomecar__wKgHHls8hiKADrqGAABK67H4HUI503.png', 'A');
INSERT INTO `t_car_brand` VALUES (272, 'ARCFOX', '//car3.autoimg.cn/cardfs/series/g27/M02/AB/F7/100x100_f40_autohomecar__ChcCQFs8nA6AP-h5AABsvxhHw3E709.png', 'A');
INSERT INTO `t_car_brand` VALUES (34, '阿尔法・罗密欧', '//car2.autoimg.cn/cardfs/series/g26/M05/B0/29/autohomecar__ChcCP1s9u5qAemANAABON_GMdvI451.png', 'A');
INSERT INTO `t_car_brand` VALUES (35, '阿斯顿・马丁', '//car2.autoimg.cn/cardfs/series/g26/M06/AE/B5/autohomecar__wKgHEVs9u6GAPWN8AAAYsmBsCWs847.png', 'A');
INSERT INTO `t_car_brand` VALUES (221, '安凯客车', '//car2.autoimg.cn/cardfs/series/g29/M00/AB/C8/100x100_f40_autohomecar__ChcCSFs8riCAYVA2AAApQLgf8a0969.png', 'A');
INSERT INTO `t_car_brand` VALUES (33, '奥迪', '//car2.autoimg.cn/cardfs/series/g26/M0B/AE/B3/autohomecar__wKgHEVs9u5WAV441AAAKdxZGE4U148.png', 'A');
INSERT INTO `t_car_brand` VALUES (140, '巴博斯', '//car2.autoimg.cn/cardfs/series/g26/M08/AF/E1/autohomecar__ChcCP1s9smyARYtBAAAbaaONnzg711.png', 'B');
INSERT INTO `t_car_brand` VALUES (120, '宝骏', '//car2.autoimg.cn/cardfs/series/g29/M05/B3/9D/autohomecar__wKgHJFs-vLaAQEDzAAA1tc2laCI414.png', 'B');
INSERT INTO `t_car_brand` VALUES (15, '宝马', '//car2.autoimg.cn/cardfs/series/g26/M0B/AF/DD/autohomecar__wKgHHVs9uuSAdz-2AAAtY7ZwY3U416.png', 'B');
INSERT INTO `t_car_brand` VALUES (231, '宝沃', '//car3.autoimg.cn/cardfs/series/g26/M05/AA/A1/autohomecar__wKgHEVs8raOAIlAJAAAsu8M_vL0825.png', 'B');
INSERT INTO `t_car_brand` VALUES (40, '保时捷', '//car3.autoimg.cn/cardfs/series/g29/M02/AF/A7/autohomecar__ChcCSFs9s3yAIrmSAAAedbUb4DQ224.png', 'B');
INSERT INTO `t_car_brand` VALUES (27, '北京', '//car2.autoimg.cn/cardfs/series/g26/M0B/AF/E1/autohomecar__wKgHHVs9u4eAGsNDAAA1F9MDvLo785.png', 'B');
INSERT INTO `t_car_brand` VALUES (301, '北汽道达', '//car3.autoimg.cn/cardfs/series/g30/M0B/A9/FB/autohomecar__wKgHHFs8guKAHq1OAAAuyvGl_RU803.png', 'B');
INSERT INTO `t_car_brand` VALUES (203, '北汽幻速', '//car3.autoimg.cn/cardfs/series/g29/M02/AF/96/autohomecar__ChcCSFs9sVuAciUFAAAormQT1CY327.png', 'B');
INSERT INTO `t_car_brand` VALUES (173, '北汽绅宝', '//car2.autoimg.cn/cardfs/series/g27/M04/AE/A9/autohomecar__wKgHE1s9seeAEsIVAAAshG9_zNk915.png', 'B');
INSERT INTO `t_car_brand` VALUES (143, '北汽威旺', '//car2.autoimg.cn/cardfs/series/g27/M06/B0/01/autohomecar__wKgHHls9sl2AS3ynAAAti2dtJX0432.png', 'B');
INSERT INTO `t_car_brand` VALUES (208, '北汽新能源', '//car3.autoimg.cn/cardfs/series/g29/M05/AB/A4/autohomecar__wKgHJFs8rx-ADw9OAAAkVu_zisE191.png', 'B');
INSERT INTO `t_car_brand` VALUES (154, '北汽制造', '//car3.autoimg.cn/cardfs/series/g30/M06/AF/58/autohomecar__wKgHHFs9vW-AEDEdAAAnsLkIq70403.png', 'B');
INSERT INTO `t_car_brand` VALUES (36, '奔驰', '//car3.autoimg.cn/cardfs/series/g26/M00/AF/E7/autohomecar__wKgHHVs9u6mAaY6mAAA2M840O5c440.png', 'B');
INSERT INTO `t_car_brand` VALUES (95, '奔腾', '//car3.autoimg.cn/cardfs/series/g3/M09/56/18/autohomecar__ChcCRVusr5-Aayp8AABgOQjSPIc243.png', 'B');
INSERT INTO `t_car_brand` VALUES (14, '本田', '//car3.autoimg.cn/cardfs/series/g29/M0B/AF/A0/autohomecar__ChcCSFs9s1iAGMiNAAAlP_CBhLY618.png', 'B');
INSERT INTO `t_car_brand` VALUES (271, '比速汽车', '//car3.autoimg.cn/cardfs/series/g27/M04/AA/91/autohomecar__wKgHE1s8nBuAMAXYAAAbuJuhhQU550.png', 'B');
INSERT INTO `t_car_brand` VALUES (75, '比亚迪', '//car2.autoimg.cn/cardfs/series/g30/M03/B0/29/autohomecar__wKgHPls9uK2AdsqZAAASbDPNPis194.png', 'B');
INSERT INTO `t_car_brand` VALUES (13, '标致', '//car2.autoimg.cn/cardfs/series/g28/M00/AE/B3/autohomecar__wKgHFFs9ut6AMOmqAAAo-NlcmyU236.png', 'B');
INSERT INTO `t_car_brand` VALUES (38, '别克', '//car3.autoimg.cn/cardfs/series/g29/M04/7F/1B/autohomecar__wKgHG1tr8RaAdw3qAAA_yVjMV8M374.png', 'B');
INSERT INTO `t_car_brand` VALUES (39, '宾利', '//car3.autoimg.cn/cardfs/series/g28/M06/AE/A5/autohomecar__wKgHFFs9uNCAOyW9AAAnxKBcMUs989.png', 'B');
INSERT INTO `t_car_brand` VALUES (37, '布加迪', '//car3.autoimg.cn/cardfs/series/g27/M07/B0/47/autohomecar__wKgHHls9u6-AR44cAAAs_DFf2AY596.png', 'B');
INSERT INTO `t_car_brand` VALUES (79, '北汽昌河', '//car2.autoimg.cn/cardfs/series/g28/M08/AF/D2/autohomecar__ChcCR1s9s-KARAAyAAAeAIvMymc980.png', 'B');
INSERT INTO `t_car_brand` VALUES (76, '长安', '//car2.autoimg.cn/cardfs/series/g30/M01/B0/2A/autohomecar__wKgHPls9uL6AX7bwAAArnGJzV54937.png', 'C');
INSERT INTO `t_car_brand` VALUES (299, '长安跨越', '//car2.autoimg.cn/cardfs/series/g29/M02/AA/69/autohomecar__wKgHJFs8gvyAIOjpAAAP8QDmnsg975.png', 'C');
INSERT INTO `t_car_brand` VALUES (163, '长安欧尚', '//car2.autoimg.cn/cardfs/series/g28/M0B/B0/13/autohomecar__ChcCR1s9vUCABiGBAAA7IcILrv4772.png', 'C');
INSERT INTO `t_car_brand` VALUES (294, '长安轻型车', '//car2.autoimg.cn/cardfs/series/g26/M00/AF/8A/autohomecar__wKgHHVs9r6OAER1OAAA4HbXEhtg696.png', 'C');
INSERT INTO `t_car_brand` VALUES (77, '长城', '//car3.autoimg.cn/cardfs/series/g30/M00/AF/12/autohomecar__wKgHHFs9s9OAOb66AAAYgXAgE6Q888.png', 'C');
INSERT INTO `t_car_brand` VALUES (196, '成功汽车', '//car2.autoimg.cn/cardfs/series/g27/M00/AC/45/autohomecar__wKgHHls8r8uATj2MAAAiLCYmGw8960.png', 'C');
INSERT INTO `t_car_brand` VALUES (169, 'DS', '//car3.autoimg.cn/cardfs/series/g28/M01/B0/76/autohomecar__wKgHI1s9vOqAZgD8AAAcfiCwiR8514.png', 'D');
INSERT INTO `t_car_brand` VALUES (92, '大发', '//car3.autoimg.cn/cardfs/series/g30/M05/B4/D1/100x100_f40_autohomecar__ChcCSVs-vGGAEaeLAAAp-wVFo0E557.png', 'D');
INSERT INTO `t_car_brand` VALUES (1, '大众', '//car3.autoimg.cn/cardfs/series/g29/M07/AF/BE/autohomecar__wKgHJFs9vGCABLhjAAAxZhBm1OY195.png', 'D');
INSERT INTO `t_car_brand` VALUES (41, '道奇', '//car3.autoimg.cn/cardfs/series/g28/M02/B0/57/autohomecar__wKgHI1s9uNeAb52AAAASYiac9j0595.png', 'D');
INSERT INTO `t_car_brand` VALUES (280, '电咖', '//car3.autoimg.cn/cardfs/series/g29/M08/AA/AD/autohomecar__ChcCSFs8hdqAOLNBAACnh0DsAE4404.png', 'D');
INSERT INTO `t_car_brand` VALUES (32, '东风', '//car2.autoimg.cn/cardfs/series/g26/M0B/B4/06/autohomecar__ChcCP1s-vICAfczWAAA6Go9ioGI755.png', 'D');
INSERT INTO `t_car_brand` VALUES (187, '东风风度', '//car2.autoimg.cn/cardfs/series/g28/M0A/AC/8F/autohomecar__wKgHFFs9bDiAMDyGAAAW7CUMgNI376.png', 'D');
INSERT INTO `t_car_brand` VALUES (259, '东风风光', '//car3.autoimg.cn/cardfs/series/g29/M07/AB/4F/autohomecar__wKgHJFs8ntuAMyzLAAAiej-Yyi4735.png', 'D');
INSERT INTO `t_car_brand` VALUES (113, '东风风神', '//car3.autoimg.cn/cardfs/series/g28/M03/A3/2C/autohomecar__ChcCR1t2aL6Ae3I9AAAkZ9PGQfU508.png', 'D');
INSERT INTO `t_car_brand` VALUES (165, '东风风行', '//car2.autoimg.cn/cardfs/series/g29/M01/AE/FB/autohomecar__wKgHG1s9vQGAVg3PAAAWc8enfOw834.png', 'D');
INSERT INTO `t_car_brand` VALUES (142, '东风小康', '//car2.autoimg.cn/cardfs/series/g29/M07/51/B9/autohomecar__ChsEflvzc-CAQAjsAAAcpo1Owuo575.png', 'D');
INSERT INTO `t_car_brand` VALUES (81, '东南', '//car2.autoimg.cn/cardfs/series/g30/M00/AF/14/autohomecar__wKgHHFs9s_KAaauXAAAa0T_XCnU027.png', 'D');
INSERT INTO `t_car_brand` VALUES (42, '法拉利', '//car3.autoimg.cn/cardfs/series/g26/M02/AF/D4/autohomecar__wKgHHVs9uRGAKwulAAAcefsPoas770.png', 'F');
INSERT INTO `t_car_brand` VALUES (11, '菲亚特', '//car2.autoimg.cn/cardfs/series/g27/M01/AE/EA/autohomecar__wKgHE1s9usmAIAd8AAA9Rhf3SVw801.png', 'F');
INSERT INTO `t_car_brand` VALUES (3, '丰田', '//car3.autoimg.cn/cardfs/series/g29/M04/AF/BE/autohomecar__wKgHJFs9vGSAY09jAAAvZAwDhiM445.png', 'F');
INSERT INTO `t_car_brand` VALUES (141, '福迪', '//car3.autoimg.cn/cardfs/series/g27/M07/AE/AD/autohomecar__wKgHE1s9smOAaFprAABBXlFoCfo726.png', 'F');
INSERT INTO `t_car_brand` VALUES (197, '福汽启腾', '//car2.autoimg.cn/cardfs/series/g30/M05/B0/98/autohomecar__ChcCSVs9sXWAQiCQAAAzC3v2Ga8780.png', 'F');
INSERT INTO `t_car_brand` VALUES (8, '福特', '//car2.autoimg.cn/cardfs/series/g29/M0B/AF/E7/autohomecar__ChcCSFs9vGmATLmRAAAoRQhUCeo248.png', 'F');
INSERT INTO `t_car_brand` VALUES (96, '福田', '//car2.autoimg.cn/cardfs/series/g28/M0B/AE/95/autohomecar__wKgHFFs9tlaAV372AAAvQCIQ85k286.png', 'F');
INSERT INTO `t_car_brand` VALUES (282, '福田乘用车', '//car2.autoimg.cn/cardfs/series/g27/M07/A9/DC/autohomecar__wKgHE1s8haaALR1GAAAjxKKcgAg658.png', 'F');
INSERT INTO `t_car_brand` VALUES (112, 'GMC', '//car3.autoimg.cn/cardfs/series/g28/M06/AE/94/autohomecar__wKgHFFs9te6AToffAAAalLxEQiw361.png', 'G');
INSERT INTO `t_car_brand` VALUES (152, '观致', '//car3.autoimg.cn/cardfs/series/g29/M09/B2/C9/autohomecar__wKgHG1s-vBOALsLAAAAwsyp19fk466.png', 'G');
INSERT INTO `t_car_brand` VALUES (116, '光冈', '//car2.autoimg.cn/cardfs/series/g27/M01/AE/F4/autohomecar__wKgHE1s9vBqAY6sWAAAQDMyOjUE817.png', 'G');
INSERT INTO `t_car_brand` VALUES (82, '广汽传祺', '//car2.autoimg.cn/cardfs/series/g30/M06/B0/B1/autohomecar__ChcCSVs9s_2AQaibAAAlsz0QLvY244.png', 'G');
INSERT INTO `t_car_brand` VALUES (108, '广汽吉奥', '//car3.autoimg.cn/cardfs/series/g26/M0B/AF/F6/autohomecar__ChcCP1s9tFuAK6vOAAAeCnZnwzg718.png', 'G');
INSERT INTO `t_car_brand` VALUES (304, '国金汽车', '//car3.autoimg.cn/cardfs/series/g27/M02/A9/BD/autohomecar__wKgHE1s8ggWAABjPAAA6CzsiZpg136.png', 'G');
INSERT INTO `t_car_brand` VALUES (24, '哈飞', '//car3.autoimg.cn/cardfs/series/g29/M01/AF/B7/autohomecar__wKgHJFs9u2KAZWMRAAAQcjoZmFg843.png', 'H');
INSERT INTO `t_car_brand` VALUES (181, '哈弗', '//car2.autoimg.cn/cardfs/series/g30/M02/AD/09/autohomecar__wKgHHFs9bJGAOO_UAAAPX0eFRSo419.png', 'H');
INSERT INTO `t_car_brand` VALUES (150, '海格', '//car2.autoimg.cn/cardfs/series/g28/M08/AE/77/autohomecar__wKgHFFs9skaAJY6ZAAAzAlgWPH8839.png', 'H');
INSERT INTO `t_car_brand` VALUES (86, '海马', '//car2.autoimg.cn/cardfs/series/g30/M00/AF/1C/autohomecar__wKgHHFs9tAiAfpCvAAAnKHYVDTM004.png', 'H');
INSERT INTO `t_car_brand` VALUES (267, '汉腾汽车', '//car3.autoimg.cn/cardfs/series/g28/M08/AB/86/autohomecar__ChcCR1s8nGqAUYohAABBmS3d_M0580.png', 'H');
INSERT INTO `t_car_brand` VALUES (43, '悍马', '//car3.autoimg.cn/cardfs/series/g29/M08/AE/BA/autohomecar__wKgHG1s9s4SASaD0AAASMGgcudg275.png', 'H');
INSERT INTO `t_car_brand` VALUES (164, '恒天', '//car2.autoimg.cn/cardfs/series/g30/M0A/B0/4B/autohomecar__wKgHPls9vTmAaP9OAAAr-Ou6L7o333.png', 'H');
INSERT INTO `t_car_brand` VALUES (91, '红旗', '//car3.autoimg.cn/cardfs/series/g26/M05/AE/94/autohomecar__wKgHEVs9tm6ASWlTAAAUz_2mWTY720.png', 'H');
INSERT INTO `t_car_brand` VALUES (245, '华凯', '//car2.autoimg.cn/cardfs/series/g29/M04/AB/97/autohomecar__ChcCSFs8o8-AKwfzAABj2CfXwSE062.png', 'H');
INSERT INTO `t_car_brand` VALUES (237, '华利', '//car3.autoimg.cn/cardfs/series/g29/M09/AB/C5/100x100_f40_autohomecar__ChcCSFs8rViAee3SAAAkdzLnYr0038.png', 'H');
INSERT INTO `t_car_brand` VALUES (85, '华普', '//car2.autoimg.cn/cardfs/series/g26/M00/AF/C1/autohomecar__wKgHHVs9toGADgreAAA_OKgWxgw893.png', 'H');
INSERT INTO `t_car_brand` VALUES (184, '华骐', '//car3.autoimg.cn/cardfs/series/g30/M00/AF/F8/autohomecar__wKgHPls9sayAb_miAAAvTHnchzM172.png', 'H');
INSERT INTO `t_car_brand` VALUES (220, '华颂', '//car2.autoimg.cn/cardfs/series/g26/M09/AF/DA/autohomecar__ChcCP1s9sReAIGj8AAAyCRkSDuI502.png', 'H');
INSERT INTO `t_car_brand` VALUES (87, '华泰', '//car2.autoimg.cn/cardfs/series/g26/M03/AF/C0/autohomecar__wKgHHVs9tneAMfJPAAAgUqGRDbI235.png', 'H');
INSERT INTO `t_car_brand` VALUES (260, '华泰新能源', '//car2.autoimg.cn/cardfs/series/g29/M01/AB/4C/autohomecar__wKgHJFs8ns6AXjhfAAAhvhXs76U833.png', 'H');
INSERT INTO `t_car_brand` VALUES (97, '黄海', '//car3.autoimg.cn/cardfs/series/g29/M0A/AF/B8/autohomecar__ChcCSFs9tk6ARTblAAAxzI_ZQ3A039.png', 'H');
INSERT INTO `t_car_brand` VALUES (188, 'Icona', '//car3.autoimg.cn/cardfs/series/g30/M04/AD/07/100x100_f40_autohomecar__wKgHHFs9bCiAVaxFAAAM-fOGPKw326.png', 'I');
INSERT INTO `t_car_brand` VALUES (46, 'Jeep', '//car3.autoimg.cn/cardfs/series/g30/M09/B0/D2/autohomecar__ChcCSVs9uSKAfIubAAAhhGdi0vw459.png', 'J');
INSERT INTO `t_car_brand` VALUES (25, '吉利汽车', '//car3.autoimg.cn/cardfs/series/g29/M06/AF/7D/autohomecar__wKgHJFs9s2SAMKFPAAAwi9ZY7aA153.png', 'J');
INSERT INTO `t_car_brand` VALUES (84, '江淮', '//car3.autoimg.cn/cardfs/series/g27/M01/B0/3D/autohomecar__ChcCQFs9touAZxvgAAAcEM6h5fk288.png', 'J');
INSERT INTO `t_car_brand` VALUES (119, '江铃', '//car3.autoimg.cn/cardfs/series/g27/M01/B0/4B/autohomecar__wKgHHls9vAmAbQlPAAAOj143nKY495.png', 'J');
INSERT INTO `t_car_brand` VALUES (210, '江铃集团轻汽', '//car2.autoimg.cn/cardfs/series/g29/M08/AB/CC/autohomecar__ChcCSFs8rwaAA4ZqAAAkxky8qCw881.png', 'J');
INSERT INTO `t_car_brand` VALUES (270, '江铃集团新能源', '//car2.autoimg.cn/cardfs/series/g27/M07/AB/E0/autohomecar__wKgHHls8nCeAGlSPAAA5iJXvG4Y041.png', 'J');
INSERT INTO `t_car_brand` VALUES (44, '捷豹', '//car3.autoimg.cn/cardfs/series/g28/M08/B0/2C/autohomecar__wKgHI1s9s4mAHx7jAAAg-scT3Cw173.png', 'J');
INSERT INTO `t_car_brand` VALUES (83, '金杯', '//car3.autoimg.cn/cardfs/series/g30/M01/B0/0E/autohomecar__wKgHPls9tAKAdrX2AAAphw4W_r4675.png', 'J');
INSERT INTO `t_car_brand` VALUES (145, '金龙', '//car3.autoimg.cn/cardfs/series/g26/M09/AF/EB/autohomecar__wKgHHVs9vIWAOXGAAAAzpOY3F9U855.png', 'J');
INSERT INTO `t_car_brand` VALUES (175, '金旅', '//car3.autoimg.cn/cardfs/series/g26/M0A/AD/BD/autohomecar__wKgHHVs9bWSAXfQrAAAVxLyBDEw442.png', 'J');
INSERT INTO `t_car_brand` VALUES (151, '九龙', '//car3.autoimg.cn/cardfs/series/g28/M01/AE/76/autohomecar__wKgHFFs9sj-AGkqEAAArzHFBvws428.png', 'J');
INSERT INTO `t_car_brand` VALUES (297, '君马汽车', '//car2.autoimg.cn/cardfs/series/g27/M07/A9/C8/autohomecar__wKgHE1s8gx2ANrhoAAA6nWo_8JA317.png', 'J');
INSERT INTO `t_car_brand` VALUES (109, 'KTM', '//car2.autoimg.cn/cardfs/series/g26/M04/B0/01/autohomecar__ChcCP1s9tgiAYT0ZAAAMMwYcMdw218.png', 'K');
INSERT INTO `t_car_brand` VALUES (156, '卡尔森', '//car2.autoimg.cn/cardfs/series/g27/M03/B0/56/autohomecar__wKgHHls9vWiAcGwOAAAi4ekMON4571.png', 'K');
INSERT INTO `t_car_brand` VALUES (224, '卡升', '//car2.autoimg.cn/cardfs/series/g28/M02/AA/9A/autohomecar__wKgHFFs8re2AN33CAAA3OWTx3kA591.png', 'K');
INSERT INTO `t_car_brand` VALUES (199, '卡威', '//car2.autoimg.cn/cardfs/series/g28/M0A/AB/EB/autohomecar__ChcCR1s8r52AE230AAAWOqKEmtw112.png', 'K');
INSERT INTO `t_car_brand` VALUES (101, '开瑞', '//car2.autoimg.cn/cardfs/series/g30/M09/AF/23/autohomecar__wKgHHFs9tiSAYP7NAAAhIYl-T54441.png', 'K');
INSERT INTO `t_car_brand` VALUES (47, '凯迪拉克', '//car3.autoimg.cn/cardfs/series/g30/M07/B0/D4/autohomecar__ChcCSVs9uSyADfmVAAAjuVlhC9w875.png', 'K');
INSERT INTO `t_car_brand` VALUES (214, '凯翼', '//car2.autoimg.cn/cardfs/series/g28/M0A/AA/9C/autohomecar__wKgHFFs8rsyALK5DAAAvhWOtHRA380.png', 'K');
INSERT INTO `t_car_brand` VALUES (219, '全球鹰', '//car2.autoimg.cn/cardfs/series/g30/M07/65/E4/autohomecar__ChcCSVtid8aAE778AAApEJCvu7E865.png', 'Q');
INSERT INTO `t_car_brand` VALUES (100, '科尼赛克', '//car2.autoimg.cn/cardfs/series/g30/M09/B4/36/autohomecar__wKgHPls-vGuARqHmAAAzgnd4H4U651.png', 'K');
INSERT INTO `t_car_brand` VALUES (9, '克莱斯勒', '//car3.autoimg.cn/cardfs/series/g27/M0A/B0/21/autohomecar__ChcCQFs9s02AYJLNAAAYKYPaXc0846.png', 'K');
INSERT INTO `t_car_brand` VALUES (241, 'LOCAL MOTORS', '//car3.autoimg.cn/cardfs/series/g28/M00/AA/6A/autohomecar__wKgHFFs8pB6Aaby5AAAPblmOpFM083.png', 'L');
INSERT INTO `t_car_brand` VALUES (118, 'Lorinser', '//car2.autoimg.cn/cardfs/series/g28/M03/B0/2A/autohomecar__wKgHI1s9szSADVbkAAAVF5caJEs349.png', 'L');
INSERT INTO `t_car_brand` VALUES (48, '兰博基尼', '//car2.autoimg.cn/cardfs/series/g30/M01/AF/3B/autohomecar__wKgHHFs9uTSAYIYPAAA059qx-5c495.png', 'L');
INSERT INTO `t_car_brand` VALUES (54, '劳斯莱斯', '//car2.autoimg.cn/cardfs/series/g28/M03/B0/62/autohomecar__wKgHI1s9umWAJxTEAAAkqzHs5s8864.png', 'L');
INSERT INTO `t_car_brand` VALUES (52, '雷克萨斯', '//car2.autoimg.cn/cardfs/series/g29/M02/B0/BE/autohomecar__ChcCSFs91WqAGpOHAABVaN6-df4803.png', 'L');
INSERT INTO `t_car_brand` VALUES (10, '雷诺', '//car3.autoimg.cn/cardfs/series/g27/M07/B0/59/autohomecar__ChcCQFs9usKAMIvkAAAcJmbdQXE902.png', 'L');
INSERT INTO `t_car_brand` VALUES (124, '理念', '//car2.autoimg.cn/cardfs/series/g30/M02/B0/A8/autohomecar__ChcCSVs9sxmAek-jAAAp2Z9dqQY376.png', 'L');
INSERT INTO `t_car_brand` VALUES (80, '力帆汽车', '//car2.autoimg.cn/cardfs/series/g29/M0A/9D/51/autohomecar__ChcCSFt03BiANx2oAAAHlvXhKPc300.png', 'L');
INSERT INTO `t_car_brand` VALUES (89, '莲花汽车', '//car3.autoimg.cn/cardfs/series/g27/M04/B0/2B/autohomecar__ChcCQFs9tBaAJ4EnAAAyFYbkbB0949.png', 'L');
INSERT INTO `t_car_brand` VALUES (78, '猎豹汽车', '//car2.autoimg.cn/cardfs/series/g28/M0A/B0/30/autohomecar__wKgHI1s9s9mAERa5AAArlsAJK-0960.png', 'L');
INSERT INTO `t_car_brand` VALUES (51, '林肯', '//car2.autoimg.cn/cardfs/series/g29/M07/AE/E6/autohomecar__wKgHG1s9uk2AfcwNAAAJP1h5KGY057.png', 'L');
INSERT INTO `t_car_brand` VALUES (53, '铃木', '//car2.autoimg.cn/cardfs/series/g28/M0B/B0/2D/autohomecar__wKgHI1s9s5CADS78AAAgk82qckg464.png', 'L');
INSERT INTO `t_car_brand` VALUES (279, '领克', '//car3.autoimg.cn/cardfs/series/g28/M0B/A9/8A/autohomecar__wKgHFFs8hfSAeEAlAAAJSAX0EFA163.png', 'L');
INSERT INTO `t_car_brand` VALUES (204, '陆地方舟', '//car3.autoimg.cn/cardfs/series/g29/M01/AF/95/autohomecar__ChcCSFs9sVKAWEgmAAAvEkEYscA543.png', 'L');
INSERT INTO `t_car_brand` VALUES (88, '陆风', '//car2.autoimg.cn/cardfs/series/g30/M03/AF/1D/autohomecar__wKgHHFs9tA-AajwkAAAgKj_1lmg884.png', 'L');
INSERT INTO `t_car_brand` VALUES (49, '路虎', '//car2.autoimg.cn/cardfs/series/g30/M05/AF/3C/autohomecar__wKgHHFs9uTuAByb_AAA0TOzXvPg367.png', 'L');
INSERT INTO `t_car_brand` VALUES (50, '路特斯', '//car3.autoimg.cn/cardfs/series/g26/M07/AF/DC/autohomecar__wKgHHVs9ukGAPJ0OAAA5qTfXAzw235.png', 'L');
INSERT INTO `t_car_brand` VALUES (56, 'MINI', '//car2.autoimg.cn/cardfs/series/g29/M08/AF/D7/autohomecar__ChcCSFs9um-AT-TMAAANglZqQg0423.png', 'M');
INSERT INTO `t_car_brand` VALUES (58, '马自达', '//car3.autoimg.cn/cardfs/series/g29/M03/AE/EA/autohomecar__wKgHG1s9uoaAEgwIAAAqsn92Rk4214.png', 'M');
INSERT INTO `t_car_brand` VALUES (57, '玛莎拉蒂', '//car3.autoimg.cn/cardfs/series/g29/M06/AE/E9/autohomecar__wKgHG1s9unuAC9zfAAAoCrieSYM032.png', 'M');
INSERT INTO `t_car_brand` VALUES (55, '迈巴赫', '//car3.autoimg.cn/cardfs/series/g28/M0B/AF/D0/autohomecar__ChcCR1s9s5WAdenKAAAcx4faZ1U654.png', 'M');
INSERT INTO `t_car_brand` VALUES (129, '迈凯伦', '//car3.autoimg.cn/cardfs/series/g26/M03/AF/EB/autohomecar__ChcCP1s9svqATR_XAAAVfSUjnMI497.png', 'M');
INSERT INTO `t_car_brand` VALUES (20, '名爵', '//car3.autoimg.cn/cardfs/series/g29/M0B/AF/DF/autohomecar__ChcCSFs9u1yAUL2nAAA653MkqF0048.png', 'M');
INSERT INTO `t_car_brand` VALUES (168, '摩根', '//car3.autoimg.cn/cardfs/series/g28/M02/B0/76/autohomecar__wKgHI1s9vPKAXf-QAAAesE7N0nA774.png', 'M');
INSERT INTO `t_car_brand` VALUES (130, '纳智捷', '//car2.autoimg.cn/cardfs/series/g26/M00/AE/76/autohomecar__wKgHEVs9svSAcF7rAAAc48YsMFE160.png', 'N');
INSERT INTO `t_car_brand` VALUES (213, '南京金龙', '//car3.autoimg.cn/cardfs/series/g27/M04/AE/A6/autohomecar__wKgHE1s9sTiAYeo1AAAkJBX-2uw544.png', 'N');
INSERT INTO `t_car_brand` VALUES (60, '讴歌', '//car3.autoimg.cn/cardfs/series/g26/M06/AF/A8/autohomecar__wKgHHVs9s5uACPfEAAAoYR1HYl0751.png', 'O');
INSERT INTO `t_car_brand` VALUES (59, '欧宝', '//car2.autoimg.cn/cardfs/series/g27/M09/AE/D0/autohomecar__wKgHE1s9tw-ARnRNAAAvxtrRDQI383.png', 'O');
INSERT INTO `t_car_brand` VALUES (146, '欧朗', '//car3.autoimg.cn/cardfs/series/g26/M08/B0/2F/autohomecar__ChcCP1s9vH6AUgYgAAATwQOlBn8743.png', 'O');
INSERT INTO `t_car_brand` VALUES (61, '帕加尼', '//car3.autoimg.cn/cardfs/series/g26/M05/B0/08/autohomecar__ChcCP1s9txyAdf1vAAAqreuyhMs315.png', 'P');
INSERT INTO `t_car_brand` VALUES (26, '奇瑞', '//car2.autoimg.cn/cardfs/series/g29/M09/AF/7F/autohomecar__wKgHJFs9s2qAawQfAAAnXgLikoM954.png', 'Q');
INSERT INTO `t_car_brand` VALUES (289, '祺智', '//car3.autoimg.cn/cardfs/series/g29/M02/AA/47/100x100_f40_autohomecar__wKgHJFs8fv-AApzlAAASDdMSNco884.png', 'Q');
INSERT INTO `t_car_brand` VALUES (122, '启辰', '//car3.autoimg.cn/cardfs/series/g28/M03/AE/BD/autohomecar__wKgHFFs9u-iAMlPPAABDdd4iWaA640.png', 'Q');
INSERT INTO `t_car_brand` VALUES (62, '起亚', '//car2.autoimg.cn/cardfs/series/g26/M04/AF/EE/autohomecar__ChcCP1s9s6GAF9URAAAjNrt50sk685.png', 'Q');
INSERT INTO `t_car_brand` VALUES (235, '前途', '//car3.autoimg.cn/cardfs/series/g27/M0B/B0/0C/autohomecar__ChcCQFs9sK2AEMPKAAA0wCagSac427.png', 'Q');
INSERT INTO `t_car_brand` VALUES (312, '庆铃汽车', '//car3.autoimg.cn/cardfs/series/g26/M06/A9/5D/autohomecar__wKgHEVs8gKmAL4sMAAAToxcVbS4598.png', 'Q');
INSERT INTO `t_car_brand` VALUES (63, '日产', '//car2.autoimg.cn/cardfs/series/g26/M02/B0/09/autohomecar__ChcCP1s9tymAIl1PAAArY6Z9BvI666.png', 'R');
INSERT INTO `t_car_brand` VALUES (19, '荣威', '//car3.autoimg.cn/cardfs/series/g29/M06/AF/B6/autohomecar__wKgHJFs9u1aAWdFqAAA0wb63zCM830.png', 'R');
INSERT INTO `t_car_brand` VALUES (174, '如虎', '//car2.autoimg.cn/cardfs/series/g28/M05/AC/96/autohomecar__wKgHFFs9bXGAVfcXAAAbJeqqT4k242.png', 'R');
INSERT INTO `t_car_brand` VALUES (296, '瑞驰新能源', '//car3.autoimg.cn/cardfs/series/g28/M07/AA/C1/autohomecar__ChcCR1s8gyqAfv9GAAAWVv00aEs559.png', 'R');
INSERT INTO `t_car_brand` VALUES (103, '瑞麒', '//car3.autoimg.cn/cardfs/series/g30/M04/AF/22/autohomecar__wKgHHFs9thOANwxXAAAbgQiJA8Q971.png', 'R');
INSERT INTO `t_car_brand` VALUES (45, 'smart', '//car3.autoimg.cn/cardfs/series/g26/M09/B1/0E/autohomecar__ChcCP1s91a6AMd5MAAAamC_2t_A917.png', 'S');
INSERT INTO `t_car_brand` VALUES (269, 'SWM斯威汽车', '//car3.autoimg.cn/cardfs/series/g27/M04/AE/9E/autohomecar__wKgHE1s9sBWAGKfYAAAhrYCIgZk349.png', 'S');
INSERT INTO `t_car_brand` VALUES (64, '萨博', '//car3.autoimg.cn/cardfs/series/g26/M08/AE/99/100x100_f40_autohomecar__wKgHEVs9tzGAaEOWAAA6O2h6_vU740.png', 'S');
INSERT INTO `t_car_brand` VALUES (205, '赛麟', '//car3.autoimg.cn/cardfs/series/g29/M04/AF/94/autohomecar__ChcCSFs9sUqAakxiAAAjZtnf7HE809.png', 'S');
INSERT INTO `t_car_brand` VALUES (68, '三菱', '//car3.autoimg.cn/cardfs/series/g27/M0A/B0/28/autohomecar__ChcCQFs9s8WAByhvAAAhhnZCIUU636.png', 'S');
INSERT INTO `t_car_brand` VALUES (149, '陕汽通家', '//car2.autoimg.cn/cardfs/series/g26/M05/B0/2E/autohomecar__ChcCP1s9vHeACLcVAAA4L3jCmCc497.png', 'S');
INSERT INTO `t_car_brand` VALUES (155, '上汽大通', '//car2.autoimg.cn/cardfs/series/g30/M01/B0/9E/autohomecar__ChcCSVs9siSAPUGsAAAuDwpS4m8061.png', 'S');
INSERT INTO `t_car_brand` VALUES (66, '世爵', '//car2.autoimg.cn/cardfs/series/g26/M07/AF/C6/autohomecar__wKgHHVs9tzmAI-3zAAAnMzRcCBI762.png', 'S');
INSERT INTO `t_car_brand` VALUES (90, '双环', '//car3.autoimg.cn/cardfs/series/g27/M04/B0/2D/autohomecar__ChcCQFs9tB6ACfenAAA1ZixM3aA045.png', 'S');
INSERT INTO `t_car_brand` VALUES (69, '双龙', '//car2.autoimg.cn/cardfs/series/g27/M08/B0/0D/autohomecar__wKgHHls9s8CAMj_LAAAnJoT2rJs708.png', 'S');
INSERT INTO `t_car_brand` VALUES (162, '思铭', '//car2.autoimg.cn/cardfs/series/g28/M07/AE/C8/autohomecar__wKgHFFs9vUiAfM7aAAAmlr1vbnw720.png', 'S');
INSERT INTO `t_car_brand` VALUES (65, '斯巴鲁', '//car3.autoimg.cn/cardfs/series/g26/M03/AE/7E/autohomecar__wKgHEVs9s6eAEeqWAAAvOOeK3u0565.png', 'S');
INSERT INTO `t_car_brand` VALUES (238, '斯达泰克', '//car3.autoimg.cn/cardfs/series/g27/M06/AC/3C/autohomecar__wKgHHls8rUmAba7NAAAM5MvFgXQ249.png', 'S');
INSERT INTO `t_car_brand` VALUES (67, '斯柯达', '//car3.autoimg.cn/cardfs/series/g29/M0B/AE/D4/autohomecar__wKgHG1s9t3CAQfVMAABHgh0VMdw516.png', 'S');
INSERT INTO `t_car_brand` VALUES (202, '泰卡特', '//car2.autoimg.cn/cardfs/series/g30/M0A/B3/36/autohomecar__wKgHHFs-v52ASCz0AAAhAdD9Zvw022.png', 'T');
INSERT INTO `t_car_brand` VALUES (133, '特斯拉', '//car2.autoimg.cn/cardfs/series/g30/M05/AF/53/autohomecar__wKgHHFs9vLqASyStAAAZuDalRXk481.png', 'T');
INSERT INTO `t_car_brand` VALUES (161, '腾势', '//car2.autoimg.cn/cardfs/series/g28/M0A/AE/C9/autohomecar__wKgHFFs9vU-AcBGfAAAmHbOv0RQ929.png', 'T');
INSERT INTO `t_car_brand` VALUES (283, 'WEY', '//car3.autoimg.cn/cardfs/series/g30/M08/AA/FA/autohomecar__wKgHPls8hMSASEehAAAPDUozSkg927.png', 'W');
INSERT INTO `t_car_brand` VALUES (102, '威麟', '//car2.autoimg.cn/cardfs/series/g30/M0B/B4/36/autohomecar__wKgHPls-vHKADs-qAAAt3zAmPZk863.png', 'W');
INSERT INTO `t_car_brand` VALUES (99, '威兹曼', '//car3.autoimg.cn/cardfs/series/g30/M00/B4/E7/autohomecar__ChcCSVs-wSOAXDr8AAApY-eNtxk084.png', 'W');
INSERT INTO `t_car_brand` VALUES (192, '潍柴英致', '//car3.autoimg.cn/cardfs/series/g28/M05/AE/6F/autohomecar__wKgHFFs9sYmAaIBOAAA6PtoAGWI541.png', 'W');
INSERT INTO `t_car_brand` VALUES (284, '蔚来', '//car3.autoimg.cn/cardfs/series/g30/M0A/AA/F9/autohomecar__wKgHPls8hLSAV28qAAAVB_4gARo749.png', 'W');
INSERT INTO `t_car_brand` VALUES (70, '沃尔沃', '//car3.autoimg.cn/cardfs/series/g29/M04/AF/C6/autohomecar__ChcCSFs9uEmAc6erAABAVTRPyQw889.png', 'W');
INSERT INTO `t_car_brand` VALUES (114, '五菱汽车', '//car2.autoimg.cn/cardfs/series/g27/M05/B4/23/autohomecar__wKgHE1s-6bOAG1hhAAAhkQEWmWU678.png', 'W');
INSERT INTO `t_car_brand` VALUES (167, '五十铃', '//car3.autoimg.cn/cardfs/series/g27/M04/B0/52/autohomecar__wKgHHls9vPqAMteSAAAMEXmpDRw203.png', 'W');
INSERT INTO `t_car_brand` VALUES (98, '西雅特', '//car2.autoimg.cn/cardfs/series/g27/M04/AE/C3/autohomecar__wKgHE1s9tDeAZE1rAAAngiw3k7Y434.png', 'X');
INSERT INTO `t_car_brand` VALUES (12, '现代', '//car3.autoimg.cn/cardfs/series/g26/M06/B4/04/autohomecar__ChcCP1s-vD6ALRHvAAAre1CMIAo299.png', 'X');
INSERT INTO `t_car_brand` VALUES (185, '新凯', '//car2.autoimg.cn/cardfs/series/g29/M02/63/47/autohomecar__wKgHJFtidpGAWJPZAAA9E8qcFEI929.png', 'X');
INSERT INTO `t_car_brand` VALUES (306, '鑫源', '//car3.autoimg.cn/cardfs/series/g28/M01/A9/67/autohomecar__wKgHFFs8geOAGlV5AAAj53UY4BM175.png', 'X');
INSERT INTO `t_car_brand` VALUES (71, '雪佛兰', '//car2.autoimg.cn/cardfs/series/g29/M03/AF/A2/autohomecar__wKgHJFs9uFKAb5uSAAAhD-fryHg510.png', 'X');
INSERT INTO `t_car_brand` VALUES (72, '雪铁龙', '//car3.autoimg.cn/cardfs/series/g29/M03/AF/A3/autohomecar__wKgHJFs9uFqAbupVAAARpC69vKE867.png', 'X');
INSERT INTO `t_car_brand` VALUES (111, '野马汽车', '//car2.autoimg.cn/cardfs/series/g26/M09/AE/8F/autohomecar__wKgHEVs9tfeATlQrAAAeBFFXL38280.png', 'Y');
INSERT INTO `t_car_brand` VALUES (110, '一汽', '//car2.autoimg.cn/cardfs/series/g26/M05/AE/90/autohomecar__wKgHEVs9tgCAP5I_AAArCAX8ty8224.png', 'Y');
INSERT INTO `t_car_brand` VALUES (144, '依维柯', '//car3.autoimg.cn/cardfs/series/g26/M0A/B0/32/autohomecar__ChcCP1s9vIyATsAFAAANX6Iaj4U522.png', 'Y');
INSERT INTO `t_car_brand` VALUES (73, '英菲尼迪', '//car3.autoimg.cn/cardfs/series/g26/M0B/AF/CD/autohomecar__wKgHHVs9uHyAcBFLAAAdmkFNQKU306.png', 'Y');
INSERT INTO `t_car_brand` VALUES (93, '永源', '//car2.autoimg.cn/cardfs/series/g27/M03/B0/30/autohomecar__ChcCQFs9tCuAB11_AAA157fRcMU839.png', 'Y');
INSERT INTO `t_car_brand` VALUES (298, '宇通客车', '//car3.autoimg.cn/cardfs/series/g29/M07/A9/BE/autohomecar__wKgHG1s8gwmAM17UAAAfreVOb_g865.png', 'Y');
INSERT INTO `t_car_brand` VALUES (263, '驭胜', '//car2.autoimg.cn/cardfs/series/g27/M09/AB/FF/autohomecar__ChcCQFs8nUuAefO8AAAWUATTLVA609.png', 'Y');
INSERT INTO `t_car_brand` VALUES (232, '御捷', '//car3.autoimg.cn/cardfs/series/g26/M0B/AB/EE/autohomecar__ChcCP1s8rZeAd9ORAAAylfi3l4U081.png', 'Y');
INSERT INTO `t_car_brand` VALUES (307, '裕路', '//car2.autoimg.cn/cardfs/series/g27/M06/AB/1F/autohomecar__ChcCQFs8gaqAPQC4AAAnTu45zdQ466.png', 'Y');
INSERT INTO `t_car_brand` VALUES (286, '云度', '//car2.autoimg.cn/cardfs/series/g28/M02/AA/CE/autohomecar__ChcCR1s8hImALELHAAALi6Jc3yg883.png', 'Y');
INSERT INTO `t_car_brand` VALUES (182, '之诺', '//car2.autoimg.cn/cardfs/series/g30/M00/AF/03/autohomecar__wKgHHFs9sbSAZmqnAAAqJUazzmY138.png', 'Z');
INSERT INTO `t_car_brand` VALUES (206, '知豆', '//car3.autoimg.cn/cardfs/series/g26/M01/AB/CC/autohomecar__wKgHHVs8rz2ALJKRAAAYO1tByac306.png', 'Z');
INSERT INTO `t_car_brand` VALUES (22, '中华', '//car3.autoimg.cn/cardfs/series/g29/M0A/AF/7C/autohomecar__wKgHJFs9s16Aafk7AAAx3MRyxis299.png', 'Z');
INSERT INTO `t_car_brand` VALUES (74, '中兴', '//car2.autoimg.cn/cardfs/series/g26/M05/AE/A0/autohomecar__wKgHEVs9uIaAedlKAAAp27V9U3w505.png', 'Z');
INSERT INTO `t_car_brand` VALUES (94, '众泰', '//car2.autoimg.cn/cardfs/series/g28/M06/B0/46/autohomecar__wKgHI1s9tl6AMohnAAAWWCxmgSE212.png', 'Z');
INSERT INTO `t_car_brand` VALUES (313, '广汽新能源', '//car3.autoimg.cn/cardfs/series/g26/M01/A9/5C/autohomecar__wKgHEVs8gJyAPn9MAAAk0MrYFwg996.png', 'G');
INSERT INTO `t_car_brand` VALUES (317, '云雀汽车', '//car3.autoimg.cn/cardfs/series/g3/M0B/36/D0/autohomecar__ChsEkVufgTyANUkSAAAsohVVNYM045.png', 'Y');
INSERT INTO `t_car_brand` VALUES (319, '捷途', '//car2.autoimg.cn/cardfs/series/g28/M03/80/8E/autohomecar__ChcCR1trp2KAcRF9AAAN6k11Pl0515.png', 'J');
INSERT INTO `t_car_brand` VALUES (326, '东风・瑞泰特', '//car3.autoimg.cn/cardfs/series/g28/M01/AA/EF/autohomecar__wKgHI1s8fxeAQ_AqAAAcqetOCNE548.png', 'D');
INSERT INTO `t_car_brand` VALUES (324, '新特汽车', '//car3.autoimg.cn/cardfs/series/g28/M0B/AA/9D/autohomecar__ChcCR1s8fyyATdzeAAAhDWuIGqs316.png', 'X');
INSERT INTO `t_car_brand` VALUES (291, '威马汽车', '//car2.autoimg.cn/cardfs/series/g30/M08/AB/91/autohomecar__ChcCSVs8g2OAYuTWAAAnVkHXFeU092.png', 'W');
INSERT INTO `t_car_brand` VALUES (275, '小鹏汽车', '//car3.autoimg.cn/cardfs/series/g28/M03/9B/50/autohomecar__ChsEfVwHZyaAQFWsAAC4zqamsno362.png', 'X');
INSERT INTO `t_car_brand` VALUES (308, 'Polestar', '//car3.autoimg.cn/cardfs/series/g27/M03/A9/B8/autohomecar__wKgHE1s8gZmAJ_gRAAAMnxbAOwU102.png', 'P');
INSERT INTO `t_car_brand` VALUES (329, '广汽集团', '//car3.autoimg.cn/cardfs/series/g29/M02/AA/47/autohomecar__wKgHJFs8fv-AApzlAAASDdMSNco884.png', 'G');
INSERT INTO `t_car_brand` VALUES (332, '欧尚汽车', '//car3.autoimg.cn/cardfs/series/g28/M01/79/05/autohomecar__ChcCR1tpQ6GASFKDAAAg6GmT6zI802.png', 'O');
INSERT INTO `t_car_brand` VALUES (222, '乔治・巴顿', '//car2.autoimg.cn/cardfs/series/g30/M04/AB/1D/autohomecar__wKgHHFs8rguAMm3jAAAmdImp3ws706.png', 'Q');
INSERT INTO `t_car_brand` VALUES (333, '北京清行', '//car3.autoimg.cn/cardfs/series/g26/M05/AA/75/autohomecar__wKgHHVs8fkuAWfQiAAA3sYlXyQc334.png', 'B');
INSERT INTO `t_car_brand` VALUES (335, 'LITE', '//car2.autoimg.cn/cardfs/series/g30/M01/AA/BD/autohomecar__wKgHPls8ffCASAnxAAAdCFaaILs568.png', 'L');
INSERT INTO `t_car_brand` VALUES (337, '容大智造', '//car2.autoimg.cn/cardfs/series/g28/M02/AA/E5/autohomecar__wKgHI1s8fbOAYutPAAAOi5RingA336.png', 'R');
INSERT INTO `t_car_brand` VALUES (336, '红星汽车', '//car3.autoimg.cn/cardfs/series/g29/M06/AA/3C/autohomecar__wKgHJFs8feaAaVdvAAAS0-MQQqo282.png', 'H');
INSERT INTO `t_car_brand` VALUES (318, '零跑汽车', '//car2.autoimg.cn/cardfs/series/g1/M01/C1/48/autohomecar__ChcCQ1wYYLSANG9rAAFaxd7KJTY949.png', 'L');
INSERT INTO `t_car_brand` VALUES (331, '欧拉', '//car3.autoimg.cn/cardfs/series/g28/M09/AF/AD/autohomecar__ChcCR1s9rxyAJ0jLAAAdncBEHpM006.png', 'O');
INSERT INTO `t_car_brand` VALUES (341, '大乘汽车', '//car3.autoimg.cn/cardfs/series/g27/M02/1C/D7/autohomecar__ChcCQFuXj2aALFhEAAAdu7mBZJo627.png', 'D');
INSERT INTO `t_car_brand` VALUES (343, '领途汽车', '//car2.autoimg.cn/cardfs/series/g1/M07/30/35/autohomecar__ChcCQ1uppBiAN0GbAAASGHwE694630.png', 'L');
INSERT INTO `t_car_brand` VALUES (345, '理想智造', '//car3.autoimg.cn/cardfs/series/g30/M06/AE/DA/autohomecar__ChcCSVvISP-AfpVFAAA_YL3KM6M738.png', 'L');
INSERT INTO `t_car_brand` VALUES (346, '罗夫哈特', '//car2.autoimg.cn/cardfs/series/g3/M0B/BB/A7/autohomecar__ChsEkVvFWpmARVvLAAAVxtw5dMM875.png', 'L');
INSERT INTO `t_car_brand` VALUES (295, 'NEVS国能汽车', '//car2.autoimg.cn/cardfs/series/g28/M05/AB/1A/autohomecar__wKgHI1s8gzaAWFsVAAASKeNZhyQ775.png', 'N');
INSERT INTO `t_car_brand` VALUES (334, '哪吒汽车', '//car3.autoimg.cn/cardfs/series/g26/M07/AA/73/autohomecar__wKgHHVs8fkKADIEGAAAj7396Pg0344.png', 'N');
INSERT INTO `t_car_brand` VALUES (351, '宝骐汽车', '//car2.autoimg.cn/cardfs/series/g3/M03/44/92/autohomecar__ChsEm1vyglqAEYJuAAA835JVzPk894.png', 'B');
INSERT INTO `t_car_brand` VALUES (356, '钧天', 'https://car2.autoimg.cn/cardfs/series/g29/M0B/AF/E7/100x100_f40_autohomecar__ChcCSFs9vGmATLmRAAAoRQhUCeo248.png', 'J');
INSERT INTO `t_car_brand` VALUES (350, '星途', '', 'X');
INSERT INTO `t_car_brand` VALUES (366, '车驰汽车', '', 'C');
INSERT INTO `t_car_brand` VALUES (309, '合众汽车', '', 'H');
INSERT INTO `t_car_brand` VALUES (362, '博郡汽车', '', 'B');
INSERT INTO `t_car_brand` VALUES (371, 'Genesis', '', 'G');
INSERT INTO `t_car_brand` VALUES (373, '几何汽车', '', 'J');
INSERT INTO `t_car_brand` VALUES (325, 'SERES', '', 'S');
INSERT INTO `t_car_brand` VALUES (339, '天际汽车', '', 'T');
INSERT INTO `t_car_brand` VALUES (374, '迈莎锐', '', 'M');
INSERT INTO `t_car_brand` VALUES (375, '银隆新能源', '', 'Y');
INSERT INTO `t_car_brand` VALUES (378, 'AUXUN傲旋', '', 'A');
INSERT INTO `t_car_brand` VALUES (358, '捷达', '', 'J');
INSERT INTO `t_car_brand` VALUES (215, '雷丁', '', 'L');
INSERT INTO `t_car_brand` VALUES (381, '迈迈', '', 'M');
INSERT INTO `t_car_brand` VALUES (382, '远程汽车', '', 'Y');
INSERT INTO `t_car_brand` VALUES (369, '国机智骏', '', 'G');
INSERT INTO `t_car_brand` VALUES (327, '爱驰', '', 'A');
INSERT INTO `t_car_brand` VALUES (387, '比德文汽车', '', 'B');
INSERT INTO `t_car_brand` VALUES (388, 'SHELBY', '', 'S');
INSERT INTO `t_car_brand` VALUES (330, '思皓', '', 'S');
INSERT INTO `t_car_brand` VALUES (392, '铂驰', '', 'B');
INSERT INTO `t_car_brand` VALUES (393, '潍柴汽车', '', 'W');
INSERT INTO `t_car_brand` VALUES (396, '新宝骏', '', 'X');
INSERT INTO `t_car_brand` VALUES (386, '汉龙汽车', '', 'H');
INSERT INTO `t_car_brand` VALUES (353, 'Karma', '', 'K');
INSERT INTO `t_car_brand` VALUES (399, '一汽凌河', '', 'Y');
INSERT INTO `t_car_brand` VALUES (376, 'HYCAN合创', '', 'H');

-- ----------------------------
-- Table structure for t_city
-- ----------------------------
DROP TABLE IF EXISTS `t_city`;
CREATE TABLE `t_city`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `city_index` int(11) NOT NULL,
  `province_id` int(11) NOT NULL,
  `name` varchar(100) CHARACTER SET gbk COLLATE gbk_chinese_ci NOT NULL DEFAULT '',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 392 CHARACTER SET = gbk COLLATE = gbk_chinese_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_city
-- ----------------------------
INSERT INTO `t_city` VALUES (1, 1, 1, '北京市');
INSERT INTO `t_city` VALUES (2, 1, 2, '天津市');
INSERT INTO `t_city` VALUES (3, 1, 3, '上海市');
INSERT INTO `t_city` VALUES (4, 1, 4, '重庆市');
INSERT INTO `t_city` VALUES (5, 1, 5, '石家庄市');
INSERT INTO `t_city` VALUES (6, 2, 5, '唐山市');
INSERT INTO `t_city` VALUES (7, 3, 5, '秦皇岛市');
INSERT INTO `t_city` VALUES (8, 4, 5, '邯郸市');
INSERT INTO `t_city` VALUES (9, 5, 5, '邢台市');
INSERT INTO `t_city` VALUES (10, 6, 5, '保定市');
INSERT INTO `t_city` VALUES (11, 7, 5, '张家口市');
INSERT INTO `t_city` VALUES (12, 8, 5, '承德市');
INSERT INTO `t_city` VALUES (13, 9, 5, '沧州市');
INSERT INTO `t_city` VALUES (14, 10, 5, '廊坊市');
INSERT INTO `t_city` VALUES (15, 11, 5, '衡水市');
INSERT INTO `t_city` VALUES (16, 1, 6, '太原市');
INSERT INTO `t_city` VALUES (17, 2, 6, '大同市');
INSERT INTO `t_city` VALUES (18, 3, 6, '阳泉市');
INSERT INTO `t_city` VALUES (19, 4, 6, '长治市');
INSERT INTO `t_city` VALUES (20, 5, 6, '晋城市');
INSERT INTO `t_city` VALUES (21, 6, 6, '朔州市');
INSERT INTO `t_city` VALUES (22, 7, 6, '晋中市');
INSERT INTO `t_city` VALUES (23, 8, 6, '运城市');
INSERT INTO `t_city` VALUES (24, 9, 6, '忻州市');
INSERT INTO `t_city` VALUES (25, 10, 6, '临汾市');
INSERT INTO `t_city` VALUES (26, 11, 6, '吕梁市');
INSERT INTO `t_city` VALUES (27, 1, 7, '台北市');
INSERT INTO `t_city` VALUES (28, 2, 7, '高雄市');
INSERT INTO `t_city` VALUES (29, 3, 7, '基隆市');
INSERT INTO `t_city` VALUES (30, 4, 7, '台中市');
INSERT INTO `t_city` VALUES (31, 5, 7, '台南市');
INSERT INTO `t_city` VALUES (32, 6, 7, '新竹市');
INSERT INTO `t_city` VALUES (33, 7, 7, '嘉义市');
INSERT INTO `t_city` VALUES (34, 8, 7, '台北县');
INSERT INTO `t_city` VALUES (35, 9, 7, '宜兰县');
INSERT INTO `t_city` VALUES (36, 10, 7, '桃园县');
INSERT INTO `t_city` VALUES (37, 11, 7, '新竹县');
INSERT INTO `t_city` VALUES (38, 12, 7, '苗栗县');
INSERT INTO `t_city` VALUES (39, 13, 7, '台中县');
INSERT INTO `t_city` VALUES (40, 14, 7, '彰化县');
INSERT INTO `t_city` VALUES (41, 15, 7, '南投县');
INSERT INTO `t_city` VALUES (42, 16, 7, '云林县');
INSERT INTO `t_city` VALUES (43, 17, 7, '嘉义县');
INSERT INTO `t_city` VALUES (44, 18, 7, '台南县');
INSERT INTO `t_city` VALUES (45, 19, 7, '高雄县');
INSERT INTO `t_city` VALUES (46, 20, 7, '屏东县');
INSERT INTO `t_city` VALUES (47, 21, 7, '澎湖县');
INSERT INTO `t_city` VALUES (48, 22, 7, '台东县');
INSERT INTO `t_city` VALUES (49, 23, 7, '花莲县');
INSERT INTO `t_city` VALUES (50, 1, 8, '沈阳市');
INSERT INTO `t_city` VALUES (51, 2, 8, '大连市');
INSERT INTO `t_city` VALUES (52, 3, 8, '鞍山市');
INSERT INTO `t_city` VALUES (53, 4, 8, '抚顺市');
INSERT INTO `t_city` VALUES (54, 5, 8, '本溪市');
INSERT INTO `t_city` VALUES (55, 6, 8, '丹东市');
INSERT INTO `t_city` VALUES (56, 7, 8, '锦州市');
INSERT INTO `t_city` VALUES (57, 8, 8, '营口市');
INSERT INTO `t_city` VALUES (58, 9, 8, '阜新市');
INSERT INTO `t_city` VALUES (59, 10, 8, '辽阳市');
INSERT INTO `t_city` VALUES (60, 11, 8, '盘锦市');
INSERT INTO `t_city` VALUES (61, 12, 8, '铁岭市');
INSERT INTO `t_city` VALUES (62, 13, 8, '朝阳市');
INSERT INTO `t_city` VALUES (63, 14, 8, '葫芦岛市');
INSERT INTO `t_city` VALUES (64, 1, 9, '长春市');
INSERT INTO `t_city` VALUES (65, 2, 9, '吉林市');
INSERT INTO `t_city` VALUES (66, 3, 9, '四平市');
INSERT INTO `t_city` VALUES (67, 4, 9, '辽源市');
INSERT INTO `t_city` VALUES (68, 5, 9, '通化市');
INSERT INTO `t_city` VALUES (69, 6, 9, '白山市');
INSERT INTO `t_city` VALUES (70, 7, 9, '松原市');
INSERT INTO `t_city` VALUES (71, 8, 9, '白城市');
INSERT INTO `t_city` VALUES (72, 9, 9, '延边朝鲜族自治州');
INSERT INTO `t_city` VALUES (73, 1, 10, '哈尔滨市');
INSERT INTO `t_city` VALUES (74, 2, 10, '齐齐哈尔市');
INSERT INTO `t_city` VALUES (75, 3, 10, '鹤岗市');
INSERT INTO `t_city` VALUES (76, 4, 10, '双鸭山市');
INSERT INTO `t_city` VALUES (77, 5, 10, '鸡西市');
INSERT INTO `t_city` VALUES (78, 6, 10, '大庆市');
INSERT INTO `t_city` VALUES (79, 7, 10, '伊春市');
INSERT INTO `t_city` VALUES (80, 8, 10, '牡丹江市');
INSERT INTO `t_city` VALUES (81, 9, 10, '佳木斯市');
INSERT INTO `t_city` VALUES (82, 10, 10, '七台河市');
INSERT INTO `t_city` VALUES (83, 11, 10, '黑河市');
INSERT INTO `t_city` VALUES (84, 12, 10, '绥化市');
INSERT INTO `t_city` VALUES (85, 13, 10, '大兴安岭地区');
INSERT INTO `t_city` VALUES (86, 1, 11, '南京市');
INSERT INTO `t_city` VALUES (87, 2, 11, '无锡市');
INSERT INTO `t_city` VALUES (88, 3, 11, '徐州市');
INSERT INTO `t_city` VALUES (89, 4, 11, '常州市');
INSERT INTO `t_city` VALUES (90, 5, 11, '苏州市');
INSERT INTO `t_city` VALUES (91, 6, 11, '南通市');
INSERT INTO `t_city` VALUES (92, 7, 11, '连云港市');
INSERT INTO `t_city` VALUES (93, 8, 11, '淮安市');
INSERT INTO `t_city` VALUES (94, 9, 11, '盐城市');
INSERT INTO `t_city` VALUES (95, 10, 11, '扬州市');
INSERT INTO `t_city` VALUES (96, 11, 11, '镇江市');
INSERT INTO `t_city` VALUES (97, 12, 11, '泰州市');
INSERT INTO `t_city` VALUES (98, 13, 11, '宿迁市');
INSERT INTO `t_city` VALUES (99, 1, 12, '杭州市');
INSERT INTO `t_city` VALUES (100, 2, 12, '宁波市');
INSERT INTO `t_city` VALUES (101, 3, 12, '温州市');
INSERT INTO `t_city` VALUES (102, 4, 12, '嘉兴市');
INSERT INTO `t_city` VALUES (103, 5, 12, '湖州市');
INSERT INTO `t_city` VALUES (104, 6, 12, '绍兴市');
INSERT INTO `t_city` VALUES (105, 7, 12, '金华市');
INSERT INTO `t_city` VALUES (106, 8, 12, '衢州市');
INSERT INTO `t_city` VALUES (107, 9, 12, '舟山市');
INSERT INTO `t_city` VALUES (108, 10, 12, '台州市');
INSERT INTO `t_city` VALUES (109, 11, 12, '丽水市');
INSERT INTO `t_city` VALUES (110, 1, 13, '合肥市');
INSERT INTO `t_city` VALUES (111, 2, 13, '芜湖市');
INSERT INTO `t_city` VALUES (112, 3, 13, '蚌埠市');
INSERT INTO `t_city` VALUES (113, 4, 13, '淮南市');
INSERT INTO `t_city` VALUES (114, 5, 13, '马鞍山市');
INSERT INTO `t_city` VALUES (115, 6, 13, '淮北市');
INSERT INTO `t_city` VALUES (116, 7, 13, '铜陵市');
INSERT INTO `t_city` VALUES (117, 8, 13, '安庆市');
INSERT INTO `t_city` VALUES (118, 9, 13, '黄山市');
INSERT INTO `t_city` VALUES (119, 10, 13, '滁州市');
INSERT INTO `t_city` VALUES (120, 11, 13, '阜阳市');
INSERT INTO `t_city` VALUES (121, 12, 13, '宿州市');
INSERT INTO `t_city` VALUES (122, 13, 13, '巢湖市');
INSERT INTO `t_city` VALUES (123, 14, 13, '六安市');
INSERT INTO `t_city` VALUES (124, 15, 13, '亳州市');
INSERT INTO `t_city` VALUES (125, 16, 13, '池州市');
INSERT INTO `t_city` VALUES (126, 17, 13, '宣城市');
INSERT INTO `t_city` VALUES (127, 1, 14, '福州市');
INSERT INTO `t_city` VALUES (128, 2, 14, '厦门市');
INSERT INTO `t_city` VALUES (129, 3, 14, '莆田市');
INSERT INTO `t_city` VALUES (130, 4, 14, '三明市');
INSERT INTO `t_city` VALUES (131, 5, 14, '泉州市');
INSERT INTO `t_city` VALUES (132, 6, 14, '漳州市');
INSERT INTO `t_city` VALUES (133, 7, 14, '南平市');
INSERT INTO `t_city` VALUES (134, 8, 14, '龙岩市');
INSERT INTO `t_city` VALUES (135, 9, 14, '宁德市');
INSERT INTO `t_city` VALUES (136, 1, 15, '南昌市');
INSERT INTO `t_city` VALUES (137, 2, 15, '景德镇市');
INSERT INTO `t_city` VALUES (138, 3, 15, '萍乡市');
INSERT INTO `t_city` VALUES (139, 4, 15, '九江市');
INSERT INTO `t_city` VALUES (140, 5, 15, '新余市');
INSERT INTO `t_city` VALUES (141, 6, 15, '鹰潭市');
INSERT INTO `t_city` VALUES (142, 7, 15, '赣州市');
INSERT INTO `t_city` VALUES (143, 8, 15, '吉安市');
INSERT INTO `t_city` VALUES (144, 9, 15, '宜春市');
INSERT INTO `t_city` VALUES (145, 10, 15, '抚州市');
INSERT INTO `t_city` VALUES (146, 11, 15, '上饶市');
INSERT INTO `t_city` VALUES (147, 1, 16, '济南市');
INSERT INTO `t_city` VALUES (148, 2, 16, '青岛市');
INSERT INTO `t_city` VALUES (149, 3, 16, '淄博市');
INSERT INTO `t_city` VALUES (150, 4, 16, '枣庄市');
INSERT INTO `t_city` VALUES (151, 5, 16, '东营市');
INSERT INTO `t_city` VALUES (152, 6, 16, '烟台市');
INSERT INTO `t_city` VALUES (153, 7, 16, '潍坊市');
INSERT INTO `t_city` VALUES (154, 8, 16, '济宁市');
INSERT INTO `t_city` VALUES (155, 9, 16, '泰安市');
INSERT INTO `t_city` VALUES (156, 10, 16, '威海市');
INSERT INTO `t_city` VALUES (157, 11, 16, '日照市');
INSERT INTO `t_city` VALUES (158, 12, 16, '莱芜市');
INSERT INTO `t_city` VALUES (159, 13, 16, '临沂市');
INSERT INTO `t_city` VALUES (160, 14, 16, '德州市');
INSERT INTO `t_city` VALUES (161, 15, 16, '聊城市');
INSERT INTO `t_city` VALUES (162, 16, 16, '滨州市');
INSERT INTO `t_city` VALUES (163, 17, 16, '菏泽市');
INSERT INTO `t_city` VALUES (164, 1, 17, '郑州市');
INSERT INTO `t_city` VALUES (165, 2, 17, '开封市');
INSERT INTO `t_city` VALUES (166, 3, 17, '洛阳市');
INSERT INTO `t_city` VALUES (167, 4, 17, '平顶山市');
INSERT INTO `t_city` VALUES (168, 5, 17, '安阳市');
INSERT INTO `t_city` VALUES (169, 6, 17, '鹤壁市');
INSERT INTO `t_city` VALUES (170, 7, 17, '新乡市');
INSERT INTO `t_city` VALUES (171, 8, 17, '焦作市');
INSERT INTO `t_city` VALUES (172, 9, 17, '濮阳市');
INSERT INTO `t_city` VALUES (173, 10, 17, '许昌市');
INSERT INTO `t_city` VALUES (174, 11, 17, '漯河市');
INSERT INTO `t_city` VALUES (175, 12, 17, '三门峡市');
INSERT INTO `t_city` VALUES (176, 13, 17, '南阳市');
INSERT INTO `t_city` VALUES (177, 14, 17, '商丘市');
INSERT INTO `t_city` VALUES (178, 15, 17, '信阳市');
INSERT INTO `t_city` VALUES (179, 16, 17, '周口市');
INSERT INTO `t_city` VALUES (180, 17, 17, '驻马店市');
INSERT INTO `t_city` VALUES (181, 18, 17, '济源市');
INSERT INTO `t_city` VALUES (182, 1, 18, '武汉市');
INSERT INTO `t_city` VALUES (183, 2, 18, '黄石市');
INSERT INTO `t_city` VALUES (184, 3, 18, '十堰市');
INSERT INTO `t_city` VALUES (185, 4, 18, '荆州市');
INSERT INTO `t_city` VALUES (186, 5, 18, '宜昌市');
INSERT INTO `t_city` VALUES (187, 6, 18, '襄樊市');
INSERT INTO `t_city` VALUES (188, 7, 18, '鄂州市');
INSERT INTO `t_city` VALUES (189, 8, 18, '荆门市');
INSERT INTO `t_city` VALUES (190, 9, 18, '孝感市');
INSERT INTO `t_city` VALUES (191, 10, 18, '黄冈市');
INSERT INTO `t_city` VALUES (192, 11, 18, '咸宁市');
INSERT INTO `t_city` VALUES (193, 12, 18, '随州市');
INSERT INTO `t_city` VALUES (194, 13, 18, '仙桃市');
INSERT INTO `t_city` VALUES (195, 14, 18, '天门市');
INSERT INTO `t_city` VALUES (196, 15, 18, '潜江市');
INSERT INTO `t_city` VALUES (197, 16, 18, '神农架林区');
INSERT INTO `t_city` VALUES (198, 17, 18, '恩施土家族苗族自治州');
INSERT INTO `t_city` VALUES (199, 1, 19, '长沙市');
INSERT INTO `t_city` VALUES (200, 2, 19, '株洲市');
INSERT INTO `t_city` VALUES (201, 3, 19, '湘潭市');
INSERT INTO `t_city` VALUES (202, 4, 19, '衡阳市');
INSERT INTO `t_city` VALUES (203, 5, 19, '邵阳市');
INSERT INTO `t_city` VALUES (204, 6, 19, '岳阳市');
INSERT INTO `t_city` VALUES (205, 7, 19, '常德市');
INSERT INTO `t_city` VALUES (206, 8, 19, '张家界市');
INSERT INTO `t_city` VALUES (207, 9, 19, '益阳市');
INSERT INTO `t_city` VALUES (208, 10, 19, '郴州市');
INSERT INTO `t_city` VALUES (209, 11, 19, '永州市');
INSERT INTO `t_city` VALUES (210, 12, 19, '怀化市');
INSERT INTO `t_city` VALUES (211, 13, 19, '娄底市');
INSERT INTO `t_city` VALUES (212, 14, 19, '湘西土家族苗族自治州');
INSERT INTO `t_city` VALUES (213, 1, 20, '广州市');
INSERT INTO `t_city` VALUES (214, 2, 20, '深圳市');
INSERT INTO `t_city` VALUES (215, 3, 20, '珠海市');
INSERT INTO `t_city` VALUES (216, 4, 20, '汕头市');
INSERT INTO `t_city` VALUES (217, 5, 20, '韶关市');
INSERT INTO `t_city` VALUES (218, 6, 20, '佛山市');
INSERT INTO `t_city` VALUES (219, 7, 20, '江门市');
INSERT INTO `t_city` VALUES (220, 8, 20, '湛江市');
INSERT INTO `t_city` VALUES (221, 9, 20, '茂名市');
INSERT INTO `t_city` VALUES (222, 10, 20, '肇庆市');
INSERT INTO `t_city` VALUES (223, 11, 20, '惠州市');
INSERT INTO `t_city` VALUES (224, 12, 20, '梅州市');
INSERT INTO `t_city` VALUES (225, 13, 20, '汕尾市');
INSERT INTO `t_city` VALUES (226, 14, 20, '河源市');
INSERT INTO `t_city` VALUES (227, 15, 20, '阳江市');
INSERT INTO `t_city` VALUES (228, 16, 20, '清远市');
INSERT INTO `t_city` VALUES (229, 17, 20, '东莞市');
INSERT INTO `t_city` VALUES (230, 18, 20, '中山市');
INSERT INTO `t_city` VALUES (231, 19, 20, '潮州市');
INSERT INTO `t_city` VALUES (232, 20, 20, '揭阳市');
INSERT INTO `t_city` VALUES (233, 21, 20, '云浮市');
INSERT INTO `t_city` VALUES (234, 1, 21, '兰州市');
INSERT INTO `t_city` VALUES (235, 2, 21, '金昌市');
INSERT INTO `t_city` VALUES (236, 3, 21, '白银市');
INSERT INTO `t_city` VALUES (237, 4, 21, '天水市');
INSERT INTO `t_city` VALUES (238, 5, 21, '嘉峪关市');
INSERT INTO `t_city` VALUES (239, 6, 21, '武威市');
INSERT INTO `t_city` VALUES (240, 7, 21, '张掖市');
INSERT INTO `t_city` VALUES (241, 8, 21, '平凉市');
INSERT INTO `t_city` VALUES (242, 9, 21, '酒泉市');
INSERT INTO `t_city` VALUES (243, 10, 21, '庆阳市');
INSERT INTO `t_city` VALUES (244, 11, 21, '定西市');
INSERT INTO `t_city` VALUES (245, 12, 21, '陇南市');
INSERT INTO `t_city` VALUES (246, 13, 21, '临夏回族自治州');
INSERT INTO `t_city` VALUES (247, 14, 21, '甘南藏族自治州');
INSERT INTO `t_city` VALUES (248, 1, 22, '成都市');
INSERT INTO `t_city` VALUES (249, 2, 22, '自贡市');
INSERT INTO `t_city` VALUES (250, 3, 22, '攀枝花市');
INSERT INTO `t_city` VALUES (251, 4, 22, '泸州市');
INSERT INTO `t_city` VALUES (252, 5, 22, '德阳市');
INSERT INTO `t_city` VALUES (253, 6, 22, '绵阳市');
INSERT INTO `t_city` VALUES (254, 7, 22, '广元市');
INSERT INTO `t_city` VALUES (255, 8, 22, '遂宁市');
INSERT INTO `t_city` VALUES (256, 9, 22, '内江市');
INSERT INTO `t_city` VALUES (257, 10, 22, '乐山市');
INSERT INTO `t_city` VALUES (258, 11, 22, '南充市');
INSERT INTO `t_city` VALUES (259, 12, 22, '眉山市');
INSERT INTO `t_city` VALUES (260, 13, 22, '宜宾市');
INSERT INTO `t_city` VALUES (261, 14, 22, '广安市');
INSERT INTO `t_city` VALUES (262, 15, 22, '达州市');
INSERT INTO `t_city` VALUES (263, 16, 22, '雅安市');
INSERT INTO `t_city` VALUES (264, 17, 22, '巴中市');
INSERT INTO `t_city` VALUES (265, 18, 22, '资阳市');
INSERT INTO `t_city` VALUES (266, 19, 22, '阿坝藏族羌族自治州');
INSERT INTO `t_city` VALUES (267, 20, 22, '甘孜藏族自治州');
INSERT INTO `t_city` VALUES (268, 21, 22, '凉山彝族自治州');
INSERT INTO `t_city` VALUES (269, 1, 23, '贵阳市');
INSERT INTO `t_city` VALUES (270, 2, 23, '六盘水市');
INSERT INTO `t_city` VALUES (271, 3, 23, '遵义市');
INSERT INTO `t_city` VALUES (272, 4, 23, '安顺市');
INSERT INTO `t_city` VALUES (273, 5, 23, '铜仁地区');
INSERT INTO `t_city` VALUES (274, 6, 23, '毕节地区');
INSERT INTO `t_city` VALUES (275, 7, 23, '黔西南布依族苗族自治州');
INSERT INTO `t_city` VALUES (276, 8, 23, '黔东南苗族侗族自治州');
INSERT INTO `t_city` VALUES (277, 9, 23, '黔南布依族苗族自治州');
INSERT INTO `t_city` VALUES (278, 1, 24, '海口市');
INSERT INTO `t_city` VALUES (279, 2, 24, '三亚市');
INSERT INTO `t_city` VALUES (280, 3, 24, '五指山市');
INSERT INTO `t_city` VALUES (281, 4, 24, '琼海市');
INSERT INTO `t_city` VALUES (282, 5, 24, '儋州市');
INSERT INTO `t_city` VALUES (283, 6, 24, '文昌市');
INSERT INTO `t_city` VALUES (284, 7, 24, '万宁市');
INSERT INTO `t_city` VALUES (285, 8, 24, '东方市');
INSERT INTO `t_city` VALUES (286, 9, 24, '澄迈县');
INSERT INTO `t_city` VALUES (287, 10, 24, '定安县');
INSERT INTO `t_city` VALUES (288, 11, 24, '屯昌县');
INSERT INTO `t_city` VALUES (289, 12, 24, '临高县');
INSERT INTO `t_city` VALUES (290, 13, 24, '白沙黎族自治县');
INSERT INTO `t_city` VALUES (291, 14, 24, '昌江黎族自治县');
INSERT INTO `t_city` VALUES (292, 15, 24, '乐东黎族自治县');
INSERT INTO `t_city` VALUES (293, 16, 24, '陵水黎族自治县');
INSERT INTO `t_city` VALUES (294, 17, 24, '保亭黎族苗族自治县');
INSERT INTO `t_city` VALUES (295, 18, 24, '琼中黎族苗族自治县');
INSERT INTO `t_city` VALUES (296, 1, 25, '昆明市');
INSERT INTO `t_city` VALUES (297, 2, 25, '曲靖市');
INSERT INTO `t_city` VALUES (298, 3, 25, '玉溪市');
INSERT INTO `t_city` VALUES (299, 4, 25, '保山市');
INSERT INTO `t_city` VALUES (300, 5, 25, '昭通市');
INSERT INTO `t_city` VALUES (301, 6, 25, '丽江市');
INSERT INTO `t_city` VALUES (302, 7, 25, '思茅市');
INSERT INTO `t_city` VALUES (303, 8, 25, '临沧市');
INSERT INTO `t_city` VALUES (304, 9, 25, '文山壮族苗族自治州');
INSERT INTO `t_city` VALUES (305, 10, 25, '红河哈尼族彝族自治州');
INSERT INTO `t_city` VALUES (306, 11, 25, '西双版纳傣族自治州');
INSERT INTO `t_city` VALUES (307, 12, 25, '楚雄彝族自治州');
INSERT INTO `t_city` VALUES (308, 13, 25, '大理白族自治州');
INSERT INTO `t_city` VALUES (309, 14, 25, '德宏傣族景颇族自治州');
INSERT INTO `t_city` VALUES (310, 15, 25, '怒江傈傈族自治州');
INSERT INTO `t_city` VALUES (311, 16, 25, '迪庆藏族自治州');
INSERT INTO `t_city` VALUES (312, 1, 26, '西宁市');
INSERT INTO `t_city` VALUES (313, 2, 26, '海东地区');
INSERT INTO `t_city` VALUES (314, 3, 26, '海北藏族自治州');
INSERT INTO `t_city` VALUES (315, 4, 26, '黄南藏族自治州');
INSERT INTO `t_city` VALUES (316, 5, 26, '海南藏族自治州');
INSERT INTO `t_city` VALUES (317, 6, 26, '果洛藏族自治州');
INSERT INTO `t_city` VALUES (318, 7, 26, '玉树藏族自治州');
INSERT INTO `t_city` VALUES (319, 8, 26, '海西蒙古族藏族自治州');
INSERT INTO `t_city` VALUES (320, 1, 27, '西安市');
INSERT INTO `t_city` VALUES (321, 2, 27, '铜川市');
INSERT INTO `t_city` VALUES (322, 3, 27, '宝鸡市');
INSERT INTO `t_city` VALUES (323, 4, 27, '咸阳市');
INSERT INTO `t_city` VALUES (324, 5, 27, '渭南市');
INSERT INTO `t_city` VALUES (325, 6, 27, '延安市');
INSERT INTO `t_city` VALUES (326, 7, 27, '汉中市');
INSERT INTO `t_city` VALUES (327, 8, 27, '榆林市');
INSERT INTO `t_city` VALUES (328, 9, 27, '安康市');
INSERT INTO `t_city` VALUES (329, 10, 27, '商洛市');
INSERT INTO `t_city` VALUES (330, 1, 28, '南宁市');
INSERT INTO `t_city` VALUES (331, 2, 28, '柳州市');
INSERT INTO `t_city` VALUES (332, 3, 28, '桂林市');
INSERT INTO `t_city` VALUES (333, 4, 28, '梧州市');
INSERT INTO `t_city` VALUES (334, 5, 28, '北海市');
INSERT INTO `t_city` VALUES (335, 6, 28, '防城港市');
INSERT INTO `t_city` VALUES (336, 7, 28, '钦州市');
INSERT INTO `t_city` VALUES (337, 8, 28, '贵港市');
INSERT INTO `t_city` VALUES (338, 9, 28, '玉林市');
INSERT INTO `t_city` VALUES (339, 10, 28, '百色市');
INSERT INTO `t_city` VALUES (340, 11, 28, '贺州市');
INSERT INTO `t_city` VALUES (341, 12, 28, '河池市');
INSERT INTO `t_city` VALUES (342, 13, 28, '来宾市');
INSERT INTO `t_city` VALUES (343, 14, 28, '崇左市');
INSERT INTO `t_city` VALUES (344, 1, 29, '拉萨市');
INSERT INTO `t_city` VALUES (345, 2, 29, '那曲地区');
INSERT INTO `t_city` VALUES (346, 3, 29, '昌都地区');
INSERT INTO `t_city` VALUES (347, 4, 29, '山南地区');
INSERT INTO `t_city` VALUES (348, 5, 29, '日喀则地区');
INSERT INTO `t_city` VALUES (349, 6, 29, '阿里地区');
INSERT INTO `t_city` VALUES (350, 7, 29, '林芝地区');
INSERT INTO `t_city` VALUES (351, 1, 30, '银川市');
INSERT INTO `t_city` VALUES (352, 2, 30, '石嘴山市');
INSERT INTO `t_city` VALUES (353, 3, 30, '吴忠市');
INSERT INTO `t_city` VALUES (354, 4, 30, '固原市');
INSERT INTO `t_city` VALUES (355, 5, 30, '中卫市');
INSERT INTO `t_city` VALUES (356, 1, 31, '乌鲁木齐市');
INSERT INTO `t_city` VALUES (357, 2, 31, '克拉玛依市');
INSERT INTO `t_city` VALUES (358, 3, 31, '石河子市　');
INSERT INTO `t_city` VALUES (359, 4, 31, '阿拉尔市');
INSERT INTO `t_city` VALUES (360, 5, 31, '图木舒克市');
INSERT INTO `t_city` VALUES (361, 6, 31, '五家渠市');
INSERT INTO `t_city` VALUES (362, 7, 31, '吐鲁番市');
INSERT INTO `t_city` VALUES (363, 8, 31, '阿克苏市');
INSERT INTO `t_city` VALUES (364, 9, 31, '喀什市');
INSERT INTO `t_city` VALUES (365, 10, 31, '哈密市');
INSERT INTO `t_city` VALUES (366, 11, 31, '和田市');
INSERT INTO `t_city` VALUES (367, 12, 31, '阿图什市');
INSERT INTO `t_city` VALUES (368, 13, 31, '库尔勒市');
INSERT INTO `t_city` VALUES (369, 14, 31, '昌吉市　');
INSERT INTO `t_city` VALUES (370, 15, 31, '阜康市');
INSERT INTO `t_city` VALUES (371, 16, 31, '米泉市');
INSERT INTO `t_city` VALUES (372, 17, 31, '博乐市');
INSERT INTO `t_city` VALUES (373, 18, 31, '伊宁市');
INSERT INTO `t_city` VALUES (374, 19, 31, '奎屯市');
INSERT INTO `t_city` VALUES (375, 20, 31, '塔城市');
INSERT INTO `t_city` VALUES (376, 21, 31, '乌苏市');
INSERT INTO `t_city` VALUES (377, 22, 31, '阿勒泰市');
INSERT INTO `t_city` VALUES (378, 1, 32, '呼和浩特市');
INSERT INTO `t_city` VALUES (379, 2, 32, '包头市');
INSERT INTO `t_city` VALUES (380, 3, 32, '乌海市');
INSERT INTO `t_city` VALUES (381, 4, 32, '赤峰市');
INSERT INTO `t_city` VALUES (382, 5, 32, '通辽市');
INSERT INTO `t_city` VALUES (383, 6, 32, '鄂尔多斯市');
INSERT INTO `t_city` VALUES (384, 7, 32, '呼伦贝尔市');
INSERT INTO `t_city` VALUES (385, 8, 32, '巴彦淖尔市');
INSERT INTO `t_city` VALUES (386, 9, 32, '乌兰察布市');
INSERT INTO `t_city` VALUES (387, 10, 32, '锡林郭勒盟');
INSERT INTO `t_city` VALUES (388, 11, 32, '兴安盟');
INSERT INTO `t_city` VALUES (389, 12, 32, '阿拉善盟');
INSERT INTO `t_city` VALUES (390, 1, 33, '澳门特别行政区');
INSERT INTO `t_city` VALUES (391, 1, 34, '香港特别行政区');

-- ----------------------------
-- Table structure for t_province
-- ----------------------------
DROP TABLE IF EXISTS `t_province`;
CREATE TABLE `t_province`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) CHARACTER SET gbk COLLATE gbk_chinese_ci NULL DEFAULT NULL,
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 35 CHARACTER SET = gbk COLLATE = gbk_chinese_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of t_province
-- ----------------------------
INSERT INTO `t_province` VALUES (1, '北京市');
INSERT INTO `t_province` VALUES (2, '天津市');
INSERT INTO `t_province` VALUES (3, '上海市');
INSERT INTO `t_province` VALUES (4, '重庆市');
INSERT INTO `t_province` VALUES (5, '河北省');
INSERT INTO `t_province` VALUES (6, '山西省');
INSERT INTO `t_province` VALUES (7, '台湾省');
INSERT INTO `t_province` VALUES (8, '辽宁省');
INSERT INTO `t_province` VALUES (9, '吉林省');
INSERT INTO `t_province` VALUES (10, '黑龙江省');
INSERT INTO `t_province` VALUES (11, '江苏省');
INSERT INTO `t_province` VALUES (12, '浙江省');
INSERT INTO `t_province` VALUES (13, '安徽省');
INSERT INTO `t_province` VALUES (14, '福建省');
INSERT INTO `t_province` VALUES (15, '江西省');
INSERT INTO `t_province` VALUES (16, '山东省');
INSERT INTO `t_province` VALUES (17, '河南省');
INSERT INTO `t_province` VALUES (18, '湖北省');
INSERT INTO `t_province` VALUES (19, '湖南省');
INSERT INTO `t_province` VALUES (20, '广东省');
INSERT INTO `t_province` VALUES (21, '甘肃省');
INSERT INTO `t_province` VALUES (22, '四川省');
INSERT INTO `t_province` VALUES (23, '贵州省');
INSERT INTO `t_province` VALUES (24, '海南省');
INSERT INTO `t_province` VALUES (25, '云南省');
INSERT INTO `t_province` VALUES (26, '青海省');
INSERT INTO `t_province` VALUES (27, '陕西省');
INSERT INTO `t_province` VALUES (28, '广西壮族自治区');
INSERT INTO `t_province` VALUES (29, '西藏自治区');
INSERT INTO `t_province` VALUES (30, '宁夏回族自治区');
INSERT INTO `t_province` VALUES (31, '新疆维吾尔自治区');
INSERT INTO `t_province` VALUES (32, '内蒙古自治区');
INSERT INTO `t_province` VALUES (33, '澳门特别行政区');
INSERT INTO `t_province` VALUES (34, '香港特别行政区');

-- ----------------------------
-- Table structure for tags
-- ----------------------------
DROP TABLE IF EXISTS `tags`;
CREATE TABLE `tags`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `tagName` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户标签',
  `sortid` int(11) NULL DEFAULT NULL COMMENT '分类id',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 12 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Dynamic;

-- ----------------------------
-- Records of tags
-- ----------------------------
INSERT INTO `tags` VALUES (1, '二手车', 0);
INSERT INTO `tags` VALUES (2, '新车', 0);
INSERT INTO `tags` VALUES (3, '旧车', 1);
INSERT INTO `tags` VALUES (4, '全款', 1);
INSERT INTO `tags` VALUES (5, '分期', 2);
INSERT INTO `tags` VALUES (6, '急用', 1);
INSERT INTO `tags` VALUES (7, '便宜的', 1);
INSERT INTO `tags` VALUES (8, '贵的', 2);
INSERT INTO `tags` VALUES (9, '10-20万', 2);
INSERT INTO `tags` VALUES (10, '家用车', 1);
INSERT INTO `tags` VALUES (11, '商用车', 2);

-- ----------------------------
-- Table structure for tagsmap
-- ----------------------------
DROP TABLE IF EXISTS `tagsmap`;
CREATE TABLE `tagsmap`  (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clue_id` int(11) NULL DEFAULT NULL COMMENT '线索id',
  `tags_id` int(11) NULL DEFAULT NULL COMMENT 'tagsID',
  PRIMARY KEY (`id`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 51 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = Fixed;

-- ----------------------------
-- Records of tagsmap
-- ----------------------------
INSERT INTO `tagsmap` VALUES (1, 21, 7);
INSERT INTO `tagsmap` VALUES (2, 21, 4);
INSERT INTO `tagsmap` VALUES (3, 22, 10);
INSERT INTO `tagsmap` VALUES (4, 22, 7);
INSERT INTO `tagsmap` VALUES (5, 22, 6);
INSERT INTO `tagsmap` VALUES (6, 22, 4);
INSERT INTO `tagsmap` VALUES (7, 23, 4);
INSERT INTO `tagsmap` VALUES (8, 23, 7);
INSERT INTO `tagsmap` VALUES (9, 23, 6);
INSERT INTO `tagsmap` VALUES (10, 23, 3);
INSERT INTO `tagsmap` VALUES (11, 24, 10);
INSERT INTO `tagsmap` VALUES (12, 24, 7);
INSERT INTO `tagsmap` VALUES (13, 24, 6);
INSERT INTO `tagsmap` VALUES (14, 24, 4);
INSERT INTO `tagsmap` VALUES (15, 25, 4);
INSERT INTO `tagsmap` VALUES (16, 25, 7);
INSERT INTO `tagsmap` VALUES (17, 25, 6);
INSERT INTO `tagsmap` VALUES (18, 26, 3);
INSERT INTO `tagsmap` VALUES (19, 26, 4);
INSERT INTO `tagsmap` VALUES (20, 26, 6);
INSERT INTO `tagsmap` VALUES (21, 27, 10);
INSERT INTO `tagsmap` VALUES (22, 27, 7);
INSERT INTO `tagsmap` VALUES (23, 27, 6);
INSERT INTO `tagsmap` VALUES (24, 28, 6);
INSERT INTO `tagsmap` VALUES (25, 28, 7);
INSERT INTO `tagsmap` VALUES (26, 28, 4);
INSERT INTO `tagsmap` VALUES (27, 29, 4);
INSERT INTO `tagsmap` VALUES (28, 29, 6);
INSERT INTO `tagsmap` VALUES (29, 30, 6);
INSERT INTO `tagsmap` VALUES (30, 30, 7);
INSERT INTO `tagsmap` VALUES (31, 31, 6);
INSERT INTO `tagsmap` VALUES (32, 31, 7);
INSERT INTO `tagsmap` VALUES (33, 32, 6);
INSERT INTO `tagsmap` VALUES (34, 32, 4);
INSERT INTO `tagsmap` VALUES (35, 33, 6);
INSERT INTO `tagsmap` VALUES (36, 33, 4);
INSERT INTO `tagsmap` VALUES (37, 34, 6);
INSERT INTO `tagsmap` VALUES (38, 34, 4);
INSERT INTO `tagsmap` VALUES (39, 35, 10);
INSERT INTO `tagsmap` VALUES (40, 35, 7);
INSERT INTO `tagsmap` VALUES (41, 35, 3);
INSERT INTO `tagsmap` VALUES (42, 35, 4);
INSERT INTO `tagsmap` VALUES (43, 35, 6);
INSERT INTO `tagsmap` VALUES (44, 36, 11);
INSERT INTO `tagsmap` VALUES (45, 36, 5);
INSERT INTO `tagsmap` VALUES (46, 36, 8);
INSERT INTO `tagsmap` VALUES (47, 36, 9);
INSERT INTO `tagsmap` VALUES (48, 37, 6);
INSERT INTO `tagsmap` VALUES (49, 37, 7);
INSERT INTO `tagsmap` VALUES (50, 37, 4);

-- ----------------------------
-- Table structure for user
-- ----------------------------
DROP TABLE IF EXISTS `user`;
CREATE TABLE `user`  (
  `id` bigint(20) UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户真实姓名',
  `nickname` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '昵称',
  `avatar` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '头像',
  `phone_number` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '手机号',
  `email` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '电子邮箱',
  `brief_introduction` varchar(500) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '简介',
  `yesapi_guanggaobao_user_password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL,
  `login_way_id` int(5) NULL DEFAULT 0 COMMENT '登录方式(外键)',
  `character_id` int(5) NULL DEFAULT NULL COMMENT '用户角色(外键)',
  `range_id` int(5) NULL DEFAULT NULL COMMENT '服务性质(外键)',
  `create_time` int(11) NULL DEFAULT NULL COMMENT '创建时间',
  `delete_time` int(11) NULL DEFAULT NULL COMMENT '删除时间',
  `openid` varchar(32) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL COMMENT '微信openid',
  `refresh_token` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '用户刷新access_token',
  `balance` decimal(10, 2) NULL DEFAULT 0.00 COMMENT '余额',
  `upClueNum` int(10) NULL DEFAULT 0 COMMENT '已上传线索条数',
  `type` int(1) NULL DEFAULT 1 COMMENT '1 个人 2 公司',
  `companyName` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NULL DEFAULT NULL COMMENT '公司名称',
  `releaseNum` int(11) NULL DEFAULT 0 COMMENT '发布条数',
  PRIMARY KEY (`id`, `openid`) USING BTREE,
  UNIQUE INDEX `openid`(`openid`) USING BTREE
) ENGINE = MyISAM AUTO_INCREMENT = 18 CHARACTER SET = utf8 COLLATE = utf8_general_ci ROW_FORMAT = DYNAMIC;

-- ----------------------------
-- Records of user
-- ----------------------------
INSERT INTO `user` VALUES (17, '罗川', '^o^    คิดถึง', 'https://thirdwx.qlogo.cn/mmopen/vi_32/MuwreT5kKD5icQ6KcqiajQp3rMnceiaXqUsicguH3WpR1ibFSXw1Fg7ic5tMspXSGnRf5Degl2Avzgibb4x33jtR6ibhvg/132', '15883854381', NULL, NULL, NULL, 0, NULL, NULL, 1682236087, NULL, 'oYa1V6tZP8ktr7RH7kE8yznCBYXU', '67_iysZxd9tukwUnkJ345Z6xO2nMarHn27Ir3nvFLiJqAh0wnSwoT477LBi6t5FXnAVHkUGRRC244KU1f9WOeoSefcBm00zVSdYiGR1QeQDOlI', 0.00, 4, 1, NULL, NULL);

SET FOREIGN_KEY_CHECKS = 1;
