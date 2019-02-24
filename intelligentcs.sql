-- phpMyAdmin SQL Dump
-- version 4.6.4
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: 2019-01-20 00:24:19
-- 服务器版本： 5.7.12
-- PHP Version: 7.1.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `intelligentcs`
--

DELIMITER $$
--
-- 存储过程
--
CREATE DEFINER=`root`@`localhost` PROCEDURE `clean` ()  BEGIN
UPDATE datastatistics SET `querynum`=0, `waited`=0,`conversationAll`=0, `waitedAll`=0,`inaccessed`=0,`unaccessed`=0,`satisfaction`=100,`participatenum`=0 WHERE D_ID=1;
TRUNCATE TABLE statistics;
end$$

CREATE DEFINER=`root`@`localhost` PROCEDURE `insertDate` ()  BEGIN
INSERT INTO statistics (`S_Name`,`S_Num`,`S_Time`) SELECT "总会话量" ,`conversationAll`,unix_timestamp(now()) FROM datastatistics;
INSERT INTO statistics (`S_Name`,`S_Num`,`S_Time`) SELECT "已接入会话量" ,`inaccessed`,unix_timestamp(now()) FROM datastatistics;
INSERT INTO statistics (`S_Name`,`S_Num`,`S_Time`) SELECT "未接入会话量" ,`unaccessed`,unix_timestamp(now()) FROM datastatistics;
INSERT INTO statistics (`S_Name`,`S_Num`,`S_Time`) SELECT "排队量" ,`waited`,unix_timestamp(now()) FROM datastatistics;
END$$

DELIMITER ;

-- --------------------------------------------------------

--
-- 表的结构 `accountstatus`
--

CREATE TABLE `accountstatus` (
  `AS_ID` int(11) NOT NULL COMMENT '自增主键',
  `AS_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '账号状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `accountstatus`
--

INSERT INTO `accountstatus` (`AS_ID`, `AS_Name`) VALUES
(0, '禁用'),
(1, '正常'),
(2, '未验证');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `adminhome`
-- (See below for the actual view)
--
CREATE TABLE `adminhome` (
`A_Address` varchar(100)
,`num` bigint(21)
);

-- --------------------------------------------------------

--
-- 表的结构 `adminleavewords`
--

CREATE TABLE `adminleavewords` (
  `L_ID` int(11) NOT NULL COMMENT '自增id',
  `U_ID` int(11) NOT NULL COMMENT '用户ID',
  `L_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户姓名',
  `L_NikeName` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户称呼',
  `L_Phone` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '用户电话',
  `L_Email` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户邮箱',
  `L_Details` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '用户留言',
  `L_DetailsTime` int(11) NOT NULL COMMENT '用户留言时间',
  `L_ServerName` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '回复客服名称',
  `L_Reply` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '客服回复',
  `L_ReplyTime` int(11) NOT NULL COMMENT '客服回复时间',
  `L_ProcessingState` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '留言处理状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `adminleavewords`
--

INSERT INTO `adminleavewords` (`L_ID`, `U_ID`, `L_Name`, `L_NikeName`, `L_Phone`, `L_Email`, `L_Details`, `L_DetailsTime`, `L_ServerName`, `L_Reply`, `L_ReplyTime`, `L_ProcessingState`) VALUES
(1, 0, '1', '陈现实', '1', '1', '1', 1546966633, '1', '对于这个问题我们也不知道鸭', 1546966819, '已处理'),
(2, 0, '1', '2', '2', '2', '2', 1546966637, '123', '你才2', 1546994830, '已处理'),
(3, 0, '1', '33', '3', '3', '3', 1546966643, '无', '无', 0, '未处理'),
(4, 1, '1', '1', '1', '1', '1', 1547129017, '无', '无', 0, '未处理'),
(5, 1, '1', '1', '1', '1', '1', 1547129045, '无', '无', 0, '未处理'),
(6, 1, '哈哈哈哈哈', '1', '1', '1', '1', 1547129076, '无', '无', 0, '未处理'),
(7, 1, '哈哈哈哈哈', '1', '1', '1', '1', 1547356109, '无', '无', 0, '未处理'),
(8, 3, '3', '1', '1', '1', '1', 1547570913, '无', '无', 0, '未处理'),
(9, 2, '123', '1', '1', '1', '测试内容1', 1547571179, '1111', '回复内容1', 1547601579, '已处理'),
(10, 2, '123', '2', '2', '2', '测试内容2', 1547571427, '无', '无', 0, '未处理');

-- --------------------------------------------------------

--
-- 表的结构 `admins`
--

CREATE TABLE `admins` (
  `A_ID` int(11) NOT NULL COMMENT '自增主键',
  `A_ImageSrc` varchar(255) NOT NULL COMMENT '头像路径',
  `A_Name` varchar(16) NOT NULL COMMENT '用户名',
  `A_Password` varchar(100) NOT NULL COMMENT '用户密码',
  `A_Email` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '管理员邮箱',
  `A_FirmID` int(11) NOT NULL COMMENT '企业名称',
  `A_Group` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '分组',
  `A_CheckStatues` int(11) NOT NULL COMMENT '管理员账号状态;0:禁用,1:正常,2:未验证',
  `A_Sex` tinyint(2) NOT NULL COMMENT '性别;0:保密,1:男,2:女',
  `A_Address` varchar(100) NOT NULL COMMENT '地址',
  `A_Signature` varchar(100) NOT NULL COMMENT '个性签名',
  `A_StateID` int(11) NOT NULL COMMENT '状态（在线、离开、离线、忙碌）',
  `A_Level` tinyint(2) NOT NULL COMMENT '等级;1:超级管理员,2:管理员,3客服',
  `A_Maximum` int(11) NOT NULL COMMENT '最大接待值',
  `A_Credibility` int(11) NOT NULL COMMENT '信誉分',
  `A_Experience` int(11) NOT NULL COMMENT '用户给客服的综合体验',
  `A_Attitude` int(11) NOT NULL COMMENT '用户给客服的态度评分',
  `A_LastIP` varchar(15) NOT NULL COMMENT '最后登录ip',
  `A_LastTime` int(11) NOT NULL COMMENT '最后登录时间',
  `currentReception` int(11) NOT NULL COMMENT '客服当前接待量',
  `allReception` int(10) DEFAULT NULL COMMENT '累积会话量',
  `allConversation` int(10) DEFAULT NULL COMMENT '累积消息量',
  `CumulativeTime` int(10) DEFAULT NULL COMMENT '累积在线时长',
  `A_Createtime` int(11) NOT NULL COMMENT '创建时间',
  `A_Updatetime` int(11) NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `admins`
--

INSERT INTO `admins` (`A_ID`, `A_ImageSrc`, `A_Name`, `A_Password`, `A_Email`, `A_FirmID`, `A_Group`, `A_CheckStatues`, `A_Sex`, `A_Address`, `A_Signature`, `A_StateID`, `A_Level`, `A_Maximum`, `A_Credibility`, `A_Experience`, `A_Attitude`, `A_LastIP`, `A_LastTime`, `currentReception`, `allReception`, `allConversation`, `CumulativeTime`, `A_Createtime`, `A_Updatetime`) VALUES
(1, 'centos2.huangdf.com/thinkcmf/public/upload/20190113/99d22e28b039ceeb1bb0fce319c42995.jpg', '陈祥佳', '7c222fb2927d828af22f592134e8932480637c0d', '1150119@qq.com', 2, '0', 1, 2, '111', '这11111', 2, 1, 100, 100, 100, 100, '172.17.146.122', 1542675208, 6, 46, 11, 0, 1542675208, 1542675208),
(33, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '2', '7c222fb2927d828af22f592134e8932480637c0d', '1@qq.com', 1, '2', 1, 0, 'null', 'null', 1, 2, 3, 100, 100, 100, '172.17.145.184', 1546226046, 0, 0, 0, 0, 1546226046, 1546226046),
(40, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '456', '7c222fb2927d828af22f592134e8932480637c0d', '111@qq.om', 1, '2', 1, 0, 'null', 'null', 1, 3, 4, 100, 100, 100, '172.17.145.184', 1546195378, 0, 0, 0, 0, 1546195378, 1546195378),
(46, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '256', '7c222fb2927d828af22f592134e8932480637c0d', '123@qq.com', 1, '0', 2, 0, 'null', 'null', 1, 3, 8, 100, 100, 100, '172.17.145.184', 1546395360, 0, 0, 0, 0, 1546395360, 1546395360),
(49, 'centos2.huangdf.com/thinkcmf/public/upload/20190116/933f241cd8f07aadea4fa9ac53a7c33f.jpg', '041', '7c222fb2927d828af22f592134e8932480637c0d', '1970874980@qq.com', 1, '1', 1, 1, '广东惠州', '我设置了个性签名，但我还是很懒呀！', 2, 2, 3, 98, 98, 98, '172.17.146.6', 1547146487, 0, 0, 0, 0, 1547146487, 1547146487),
(50, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '1', '7c222fb2927d828af22f592134e8932480637c0d', '1', 11, '0', 1, 0, 'null', 'null', 1, 1, 100, 100, 100, 100, '172.17.146.121', 1547317304, 0, 0, NULL, 0, 1547317304, 1547317304),
(51, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '我是陈祥佳', '7c222fb2927d828af22f592134e8932480637c0d', '1150814189@qq.com', 12, '0', 1, 0, 'null', 'null', 1, 1, 100, 100, 100, 100, '172.17.146.121', 1547317396, 0, 0, NULL, 0, 1547317396, 1547317396),
(52, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '曹日林', '7c222fb2927d828af22f592134e8932480637c0d', '2010624007@qq.com', 13, '0', 1, 0, 'null', 'null', 1, 1, 100, 100, 100, 100, '172.18.215.19', 1547426698, 0, 1, 0, 0, 1547426698, 1547426698),
(54, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '1111', '7c4a8d09ca3762af61e59520943dc26494f8941b', '1292643752@qq.com', 15, '0', 1, 0, 'null', 'null', 1, 1, 100, 100, 100, 100, '172.17.145.201', 1547611510, 3, 7, 1, 0, 1547611510, 1547611510),
(55, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '11', '7c222fb2927d828af22f592134e8932480637c0d', '1784104114@qq.com', 16, '0', 1, 0, 'null', 'null', 1, 1, 100, 100, 100, 100, '172.18.215.19', 1547600679, 0, 0, 0, 0, 1547600679, 1547600679);

-- --------------------------------------------------------

--
-- 表的结构 `admintooken`
--

CREATE TABLE `admintooken` (
  `AT_ID` int(11) NOT NULL COMMENT '自增ID',
  `AT_Admin_ID` int(11) NOT NULL COMMENT '管理员id',
  `AT_Admin_Expire_Time` int(11) NOT NULL COMMENT '过期时间',
  `AT_Admin_Create_Time` int(11) NOT NULL COMMENT '创建时间',
  `AT_Admin_Token` varchar(64) NOT NULL COMMENT 'token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `admintooken`
--

INSERT INTO `admintooken` (`AT_ID`, `AT_Admin_ID`, `AT_Admin_Expire_Time`, `AT_Admin_Create_Time`, `AT_Admin_Token`) VALUES
(1, 1, 1563328195, 1547776195, '151232fabc43f28ac137f02c991ab0cd033fd2096dde0b92ed4b1e3a15ae241d'),
(2, 4, 1561963200, 1546411200, 'a9fd58022c8227d513f0d8f04b2422b09c08a8ecae3ad4657206d090366eeb0f'),
(3, 2, 1562546695, 1546994695, '5dc6bd318ce4cfe8ea25773ea9bcb4d69339b5221d2f527813d72c141327cddf'),
(4, 40, 1562729147, 1547177147, '0dd1efcf8a3aebac8de94c4b9a70248ef4c7a2ea78c4215db938ae3d89cd083e'),
(5, 47, 1562739652, 1547187652, 'd44028887d8adc4a87415ff127f749ddbd6d88d52df68b86796e29ef01c096a8'),
(6, 41, 1562550666, 1546998666, '45b1a1a751c30a98dab4dd2d5847d7626c4401322175b9d8a55106dab6804055'),
(7, 33, 1562739634, 1547187634, '1529763681eb1c7906d2f1926105ad545e73b8de9cd48d44ec69035a988864ee'),
(8, 49, 1563248840, 1547696840, '71a148f3f827396455fe681728510eb7c3a5d806fa203dede4054c2776f8760d'),
(9, 52, 1563176564, 1547624564, 'f64f6b42b442893d7f6d9148ca202aa85956762b95bf10a9146653fd45cbaaca'),
(10, 50, 1563172589, 1547620589, '246a51217454c25028a3cbe3a807afa440f412814cba1949eb4e0405a42d6ae9'),
(11, 53, 1563099635, 1547547635, '9d54eab0ac23cab77c5ef24930130c204754cb6f4126b2726980cd62dc88d4a8'),
(12, 54, 1563176529, 1547624529, '8216967f8e237a835f3ea87e7ce50ee1a14f17ae1fb19982ee1c7ca5e316be20');

-- --------------------------------------------------------

--
-- 替换视图以便查看 `allDate`
-- (See below for the actual view)
--
CREATE TABLE `allDate` (
`A_ID` int(11)
,`A_Name` varchar(16)
,`A_Email` varchar(255)
,`A_Maximum` int(11)
,`A_Credibility` int(11)
,`A_Experience` int(11)
,`A_Attitude` int(11)
,`G_Name` varchar(16)
,`S_Name` varchar(16)
,`AS_Name` varchar(16)
,`RT_Name` varchar(16)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `allgroup`
-- (See below for the actual view)
--
CREATE TABLE `allgroup` (
`A_ID` int(11)
,`A_Name` varchar(16)
,`G_Name` varchar(16)
,`G_ID` int(11)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `allRole`
-- (See below for the actual view)
--
CREATE TABLE `allRole` (
`R_ID` int(11)
,`R_Name` varchar(16)
,`RT_Name` varchar(16)
,`R_Description` varchar(255)
);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `cardsbody`
-- (See below for the actual view)
--
CREATE TABLE `cardsbody` (
`G_ID` int(11)
,`G_Name` varchar(16)
,`A_Name` text
);

-- --------------------------------------------------------

--
-- 表的结构 `classify`
--

CREATE TABLE `classify` (
  `id` int(19) NOT NULL COMMENT '知识分类id,唯一',
  `name` varchar(255) NOT NULL COMMENT '知识分类名称',
  `pid` int(10) NOT NULL COMMENT '当前知识分类的上一级分类，为0时该知识分类为一级分类',
  `usedit` varchar(255) NOT NULL COMMENT '是否可以编辑',
  `level` int(10) NOT NULL COMMENT '属于知识分类的几级分类',
  `value` int(10) NOT NULL COMMENT '用于选择知识分类，与ID数值相同',
  `label` varchar(255) NOT NULL COMMENT '知识分类名称，用于选择知识分类'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `classify`
--

INSERT INTO `classify` (`id`, `name`, `pid`, `usedit`, `level`, `value`, `label`) VALUES
(1, '中国', 0, 'false', 1, 1, '中国'),
(2, '广州', 1, 'false', 2, 2, '广州'),
(3, '佛山', 1, 'false', 2, 3, '佛山'),
(4, '南海', 3, 'false', 3, 4, '南海'),
(5, '狮山', 4, 'false', 4, 5, '狮山'),
(6, '广轻', 5, 'false', 5, 6, '广轻'),
(7, '天河', 2, 'false', 3, 7, '天河'),
(8, '荔湾', 2, 'false', 3, 8, '荔湾'),
(9, '科技', 0, 'false', 1, 9, '科技'),
(10, '手机', 9, 'false', 2, 10, '手机'),
(11, 'iPhone', 10, 'false', 3, 11, 'iPhone'),
(12, '华为', 10, 'false', 3, 12, '华为'),
(129, '官方', 0, 'false', 1, 129, '官方'),
(14, '南海校区', 5, 'false', 5, 14, '南海校区'),
(16, '测试', 0, 'false', 1, 16, '测试'),
(18, '11', 17, 'false', 2, 18, '11'),
(19, '111', 18, 'false', 3, 19, '111'),
(20, '1111', 19, 'false', 4, 20, '1111'),
(21, '12345', 20, 'false', 5, 21, '12345'),
(125, '新增分类', 122, 'false', 2, 125, '新增分类'),
(119, '新增分类', 118, 'false', 2, 119, '新增分类'),
(24, '111', 23, 'false', 3, 24, '111'),
(25, '1111', 24, 'false', 4, 25, '1111'),
(26, '221', 25, 'false', 5, 26, '221'),
(84, '新增分类', 2, 'false', 3, 84, '新增分类'),
(29, '好', 28, 'false', 2, 29, '好'),
(105, '0', 104, 'false', 4, 105, '0'),
(106, '0', 105, 'false', 5, 106, '0'),
(49, '1', 31, 'false', 2, 49, '1'),
(100, '0', 99, 'false', 4, 100, '0'),
(101, '0', 100, 'false', 5, 101, '0'),
(80, '新增分', 7, 'false', 4, 80, '新增分'),
(82, '啊', 81, 'false', 2, 82, '啊'),
(83, '啊', 82, 'false', 3, 83, '啊'),
(87, '1', 85, 'false', 2, 87, '1'),
(126, '新增分类', 1, 'false', 2, 126, '新增分类'),
(88, '222', 22, 'false', 2, 88, '222'),
(104, '0', 103, 'false', 3, 104, '0'),
(103, '0', 102, 'false', 2, 103, '0'),
(108, '0', 0, 'false', 1, 108, '0'),
(97, '1', 96, 'false', 2, 97, '1'),
(98, '0', 96, 'false', 2, 98, '0'),
(99, '0', 98, 'false', 3, 99, '0'),
(118, 'ssss', 0, 'false', 1, 118, 'ssss'),
(128, '南海', 1, 'false', 2, 128, '南海');

-- --------------------------------------------------------

--
-- 表的结构 `datastatistics`
--

CREATE TABLE `datastatistics` (
  `D_ID` int(10) NOT NULL COMMENT '自增ID',
  `querynum` int(10) NOT NULL COMMENT '正在查询人数',
  `waited` int(10) NOT NULL COMMENT '正在排队人数',
  `conversationAll` int(10) NOT NULL COMMENT '今日会话量',
  `waitedAll` int(10) NOT NULL COMMENT '今日排队总数',
  `inaccessed` int(10) NOT NULL COMMENT '今日已经接入会话量',
  `unaccessed` int(10) NOT NULL COMMENT '今日未接入会话量',
  `satisfaction` int(10) NOT NULL COMMENT '今日相对满意度',
  `participatenum` int(11) NOT NULL COMMENT '参评人数'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `datastatistics`
--

INSERT INTO `datastatistics` (`D_ID`, `querynum`, `waited`, `conversationAll`, `waitedAll`, `inaccessed`, `unaccessed`, `satisfaction`, `participatenum`) VALUES
(1, 0, 0, 0, 0, 0, 0, 100, 0);

-- --------------------------------------------------------

--
-- 表的结构 `favorites`
--

CREATE TABLE `favorites` (
  `F_ID` int(11) NOT NULL,
  `F_Messages` varchar(255) DEFAULT NULL COMMENT '消息',
  `F_MessagesTypes` int(4) DEFAULT NULL COMMENT '消息类型',
  `F_Time` int(11) DEFAULT NULL COMMENT '收藏时间',
  `F_UserID` int(11) DEFAULT NULL COMMENT '收藏者id'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

-- --------------------------------------------------------

--
-- 表的结构 `firm`
--

CREATE TABLE `firm` (
  `F_ID` int(11) NOT NULL COMMENT '自增主键',
  `F_Name` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '企业名称',
  `F_CreateEmail` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '企业创建人邮箱',
  `F_Createtime` int(255) NOT NULL COMMENT '创建时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `firm`
--

INSERT INTO `firm` (`F_ID`, `F_Name`, `F_CreateEmail`, `F_Createtime`) VALUES
(2, '3', '115081489@qq.com', 1542675208),
(3, '1', '1@qq.com', 1542676409),
(4, '1', '1@qq.com', 1542676974),
(6, '5', '5@qq.com', 1542677414),
(7, 'q', '393380766@qq.com', 1546913034),
(8, '亲爱', 'qq.com', 1547316297),
(11, '8', '1', 1547317304),
(12, '999', '1150814189@qq.com', 1547317396),
(13, '广东轻工职业技术学院', '2010624007@qq.com', 1547426698),
(14, '041', '1970874980@qq.com', 1547504416),
(15, '1111', '1292643752@qq.com', 1547611510),
(16, '11', '1784104114@qq.com', 1547600679);

-- --------------------------------------------------------

--
-- 表的结构 `groups`
--

CREATE TABLE `groups` (
  `G_ID` int(11) NOT NULL COMMENT '自增主键',
  `G_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '组名称'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `groups`
--

INSERT INTO `groups` (`G_ID`, `G_Name`) VALUES
(0, '未分组'),
(1, '第二组'),
(2, '第一组'),
(3, '哈哈哈');

-- --------------------------------------------------------

--
-- 表的结构 `history`
--

CREATE TABLE `history` (
  `H_ID` int(10) NOT NULL COMMENT '自增ID',
  `startTime` int(10) NOT NULL COMMENT '开始时间',
  `endTime` int(10) NOT NULL COMMENT '结束时间',
  `initiator` varchar(255) NOT NULL COMMENT '会话发起方ID',
  `receiver` varchar(255) NOT NULL COMMENT '会话接收方ID',
  `conversationTime` int(10) NOT NULL COMMENT '会话时长',
  `satisfaction` int(10) NOT NULL COMMENT '满意度。未评价：>100；非常满意：90<=x<=100；满意：80<=x<90；一般满意：60<=x<80；不满意:<60',
  `id` int(1) NOT NULL COMMENT '会话发起方。0是客服，1是用户'
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

--
-- 转存表中的数据 `history`
--

INSERT INTO `history` (`H_ID`, `startTime`, `endTime`, `initiator`, `receiver`, `conversationTime`, `satisfaction`, `id`) VALUES
(1, 1547567403, 1547567447, '用户:3', '客服:1', 44, 101, 0),
(2, 1547567486, 1547567496, '用户:3', '客服:1', 10, 101, 0),
(3, 1547567750, 1547568002, '用户:3', '客服:1', 252, 101, 0),
(4, 1547568047, 1547568067, '用户:3', '客服:1', 20, 101, 0),
(5, 1547568084, 1547568093, '用户:3', '客服:1', 9, 101, 0),
(6, 1547568382, 1547568476, '用户:3', '客服:54', 94, 101, 0),
(7, 1547568613, 1547568647, '用户:', '客服:54', 34, 101, 0),
(8, 1547568673, 1547568744, '用户:3', '客服:54', 71, 101, 0),
(9, 1547568818, 1547568830, '用户:3', '客服:54', 12, 101, 0),
(10, 1547568895, 1547568952, '用户:3', '客服:54', 57, 101, 0),
(11, 1547571116, 1547571127, '用户:3', '客服:1', 11, 101, 0),
(12, 1547570275, 1547571137, '用户:2', '客服:1', 862, 101, 0),
(13, 1547571253, 1547571260, '用户:3', '客服:1', 7, 101, 0),
(14, 1547571346, 1547571595, '用户:3', '客服:', 249, 101, 0),
(15, 1547571637, 1547571703, '用户:3', '客服:1', 66, 101, 0),
(16, 1547571708, 1547571811, '用户:3', '客服:1', 103, 101, 0),
(17, 1547571847, 1547571923, '用户:3', '客服:', 76, 101, 0),
(18, 1547571981, 1547572442, '用户:3', '客服:1', 461, 101, 0),
(19, 1547571143, 1547572537, '用户:2', '客服:1', 1394, 101, 0),
(20, 1547572589, 1547572624, '用户:3', '客服:1', 35, 100, 0),
(21, 1547572752, 1547572763, '用户:3', '客服:1', 11, 101, 0),
(22, 1547572771, 1547572779, '用户:3', '客服:1', 8, 101, 0),
(23, 1547572894, 1547573119, '用户:3', '客服:1', 225, 101, 0),
(24, 1547573191, 1547573197, '用户:3', '客服:1', 6, 101, 0),
(25, 1547573225, 1547573233, '用户:3', '客服:1', 8, 101, 0),
(26, 1547573495, 1547573517, '用户:3', '客服:1', 22, 101, 0),
(27, 1547573642, 1547573651, '用户:3', '客服:1', 9, 101, 0),
(28, 1547573663, 1547573671, '用户:3', '客服:1', 8, 101, 0),
(29, 1547573823, 1547573842, '用户:3', '客服:1', 19, 101, 0),
(30, 1547574467, 1547574513, '用户:3', '客服:1', 46, 101, 0),
(31, 1547574526, 1547574603, '用户:3', '客服:1', 77, 101, 0),
(32, 1547574611, 1547574626, '用户:3', '客服:1', 15, 101, 0),
(33, 1547574707, 1547574715, '用户:3', '客服:1', 8, 101, 0),
(34, 1547574734, 1547574739, '用户:3', '客服:1', 5, 101, 0),
(35, 1547574803, 1547574866, '用户:3', '客服:1', 63, 101, 0),
(36, 1547574949, 1547574951, '用户:3', '客服:1', 2, 101, 0),
(37, 1547575011, 1547575014, '用户:3', '客服:1', 3, 101, 0),
(38, 1547575026, 1547575029, '用户:3', '客服:1', 3, 101, 0),
(39, 1547575115, 1547575124, '用户:3', '客服:1', 9, 101, 0),
(40, 1547575152, 1547575161, '用户:3', '客服:1', 9, 101, 0),
(41, 1547575204, 1547575211, '用户:3', '客服:1', 7, 101, 0),
(42, 1547575650, 1547575699, '用户:3', '客服:', 49, 101, 0),
(43, 1547575807, 1547575883, '用户:3', '客服:1', 76, 101, 0),
(44, 1547576150, 1547577442, '用户:3', '客服:1', 1292, 101, 0),
(45, 1547601411, 1547601588, '用户:2', '客服:54', 177, 101, 0),
(46, 1547603993, 1547628295, '用户:3', '客服:52', 24302, 101, 0),
(47, 1547649810, 1547652436, '用户:3', '客服:1', 2626, 101, 0),
(48, 1547654081, 1547656286, '用户:3', '客服:1', 2205, 101, 0),
(49, 1547776567, 1547776725, '用户:3', '客服:1', 158, 101, 0);

-- --------------------------------------------------------

--
-- 表的结构 `leavewords`
--

CREATE TABLE `leavewords` (
  `L_ID` int(11) NOT NULL COMMENT '自增id',
  `U_ID` int(11) NOT NULL COMMENT '用户ID',
  `L_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户姓名',
  `L_NikeName` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户称呼',
  `L_Phone` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '用户电话',
  `L_Email` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '用户邮箱',
  `L_Details` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '用户留言',
  `L_DetailsTime` int(11) NOT NULL COMMENT '用户留言时间',
  `L_ServerName` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '回复客服名称',
  `L_Reply` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '客服回复',
  `L_ReplyTime` int(11) NOT NULL COMMENT '客服回复时间',
  `L_ProcessingState` varchar(12) CHARACTER SET utf8 NOT NULL COMMENT '留言处理状态'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `leavewords`
--

INSERT INTO `leavewords` (`L_ID`, `U_ID`, `L_Name`, `L_NikeName`, `L_Phone`, `L_Email`, `L_Details`, `L_DetailsTime`, `L_ServerName`, `L_Reply`, `L_ReplyTime`, `L_ProcessingState`) VALUES
(1, 1, '1', '陈现实', '1', '1', '1', 1546966633, '1', '对于这个问题我们也不知道鸭', 1546966819, '已处理'),
(2, 0, '1', '2', '2', '2', '2', 1546966637, '123', '你才2', 1546994830, '已处理'),
(3, 0, '1', '33', '3', '3', '3', 1546966643, '无', '无', 0, '未处理'),
(4, 1, '1', '1', '1', '1', '1', 1547128725, '无', '无', 0, '未处理'),
(5, 1, '1', '1', '1', '1', '1', 1547128773, '无', '无', 0, '未处理'),
(6, 1, '1', '1', '1', '1', '1', 1547128822, '无', '无', 0, '未处理'),
(7, 1, '1', '1', '1', '1', '1', 1547128827, '无', '无', 0, '未处理'),
(8, 1, '1', '1', '1', '1', '1', 1547128897, '无', '无', 0, '未处理'),
(9, 1, '1', '1', '1', '1', '1', 1547129017, '1111', '回复内容1', 1547601579, '已处理'),
(10, 1, '1', '1', '1', '1', '1', 1547129045, '无', '无', 0, '未处理'),
(11, 1, '哈哈哈哈哈', '1', '1', '1', '1', 1547129076, '无', '无', 0, '未处理'),
(12, 1, '哈哈哈哈哈', '1', '1', '1', '1', 1547356109, '无', '无', 0, '未处理'),
(13, 3, '3', '1', '1', '1', '1', 1547570913, '无', '无', 0, '未处理'),
(14, 2, '123', '1', '1', '1', '测试内容1', 1547571179, '无', '无', 0, '未处理'),
(15, 2, '123', '2', '2', '2', '测试内容2', 1547571427, '无', '无', 0, '未处理');

-- --------------------------------------------------------

--
-- 表的结构 `messagestype`
--

CREATE TABLE `messagestype` (
  `M_Id` int(11) NOT NULL COMMENT '自增主键',
  `M_Name` varchar(16) NOT NULL COMMENT '消息类型名称、文字、图片、语音'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `messagestype`
--

INSERT INTO `messagestype` (`M_Id`, `M_Name`) VALUES
(1, '文字'),
(2, '图片');

-- --------------------------------------------------------

--
-- 表的结构 `personallibrary`
--

CREATE TABLE `personallibrary` (
  `P_ID` int(11) NOT NULL COMMENT '自增ID',
  `P_QuickWord` varchar(16) NOT NULL COMMENT '快捷词',
  `P_Reply` varchar(255) NOT NULL COMMENT '回复内容',
  `P_Classify` int(11) NOT NULL COMMENT '所属快捷分类',
  `P_Belongs` int(11) NOT NULL COMMENT '所属管理员/客服ID',
  `P_CreateTime` int(11) NOT NULL COMMENT '创建时间',
  `P_UpdateTime` int(11) NOT NULL COMMENT '更新时间	'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `personallibrary`
--

INSERT INTO `personallibrary` (`P_ID`, `P_QuickWord`, `P_Reply`, `P_Classify`, `P_Belongs`, `P_CreateTime`, `P_UpdateTime`) VALUES
(4, 'aad', 'ddd', 13, 1, 1546752878, 1546753566);

-- --------------------------------------------------------

--
-- 表的结构 `pointknowledge`
--

CREATE TABLE `pointknowledge` (
  `PK_ID` int(11) NOT NULL COMMENT '知识点自增ID',
  `PK_Name` varchar(255) NOT NULL COMMENT '知识点名称',
  `id` int(10) NOT NULL COMMENT '所属知识分类最后一级分类的ID（classify表里的唯一id）',
  `PK_Createtime` int(10) NOT NULL COMMENT '创建时间',
  `PK_Updatetime` int(10) NOT NULL COMMENT '更新时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `pointknowledge`
--

INSERT INTO `pointknowledge` (`PK_ID`, `PK_Name`, `id`, `PK_Createtime`, `PK_Updatetime`) VALUES
(3, '测试', 108, 1544855494, 1547605307);

-- --------------------------------------------------------

--
-- 表的结构 `publiclibrary`
--

CREATE TABLE `publiclibrary` (
  `P_ID` int(11) NOT NULL COMMENT '自增ID',
  `P_QuickWord` varchar(16) NOT NULL COMMENT '快捷词',
  `P_Reply` varchar(255) NOT NULL COMMENT '回复内容',
  `P_Classify` int(11) NOT NULL COMMENT '所属快捷分类',
  `P_CreateTime` int(11) NOT NULL COMMENT '创建时间',
  `P_UpdateTime` int(11) NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `publiclibrary`
--

INSERT INTO `publiclibrary` (`P_ID`, `P_QuickWord`, `P_Reply`, `P_Classify`, `P_CreateTime`, `P_UpdateTime`) VALUES
(10, 'a', 'a', 6, 1546711729, 1546753434),
(11, '2', '3', 6, 1546711734, 1546711734),
(12, '1', '1', 7, 1546754263, 1546754263),
(13, '1', '1', 8, 1546754268, 1546754268),
(14, '1', '1', 10, 1546754272, 1546754272),
(15, 'd', 'd', 8, 1546754276, 1546754276),
(16, '22', '22', 1, 1546754280, 1546754280),
(17, 'da', 'ad', 8, 1546754293, 1546754293),
(18, 'a', 'ads', 1, 1546754298, 1546754298),
(19, 'd f', 'ad ', 8, 1546754306, 1546754306),
(20, 'ddd', 'ddd', 8, 1546754316, 1546754316),
(21, '1', '1', 1, 1546831792, 1546831792);

-- --------------------------------------------------------

--
-- 表的结构 `question`
--

CREATE TABLE `question` (
  `Q_ID` int(11) NOT NULL COMMENT '问题的自增ID',
  `Q_Question` varchar(255) NOT NULL COMMENT '问题名称',
  `Q_Answer` varchar(255) NOT NULL COMMENT '问题答案',
  `Q_SimilarPro` varchar(255) DEFAULT NULL COMMENT '相似问题',
  `PK_Name` varchar(255) DEFAULT NULL COMMENT '知识点',
  `id` int(10) DEFAULT NULL COMMENT '所属知识分类的最后一级分类的ID',
  `Q_Count` int(11) DEFAULT NULL COMMENT '被提问的次数（热门问题）',
  `Q_Createtime` int(10) NOT NULL COMMENT '创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `question`
--

INSERT INTO `question` (`Q_ID`, `Q_Question`, `Q_Answer`, `Q_SimilarPro`, `PK_Name`, `id`, `Q_Count`, `Q_Createtime`) VALUES
(2, '怎样申请课程', '老师您好！有以下的一些步骤哦！\n① 点击首页导航中“申请课程”；\n\n② 填写相关课程申请信息，注意带*的是必填的选项，提交后等待管理员审批；\n\n③ 审批的结果会以邮件的形式告知用户，请用户注意查收。\n\n以下是网页版详细教程http://moodle.scnu.edu.cn/mod/page/view.php?\nid=28341#a1\n', '如何申请课程--请问如何申请课程--怎样在线申请课程--在线申请课程', '', 16, 12, 1544249896),
(3, '如何给课程添加封面图？', '进入“更改设置”的方法：快捷入口。', '', '', 16, 0, 1544249896),
(1562, '11111111', '2', '2', '', 0, 2, 1547001292),
(1563, '22222', '222222', '', '', 119, 0, 1547001823),
(4, '如何添加其他教师、课程助教？', '老师您好！有一些的一些步骤哦！\n一、给课程添加用户的前提\n给课程添加用户的前提，用户必须已经在平台建立账号。所添的用户为校内人员，需要该用户登录过砺儒云课堂（系统自动建立账号）；所添的用户为校外人员，请先联系管理员建立账号。\n\n二、添加其他教师、课程助教的方式\n1、侧边导航【系统管理】-【课程管理】-“用户”-“已选课用户”。\n2、点击右上角的“添加用户”按钮。\n3、在“加入用户”的弹框中\n    选择用户：输入相应的一卡通号/邮箱/姓/名，在弹出的下拉框中点击正确的用户名称。注意：姓名不是搜索项。\n  ', '怎样添加其他教师、课程助教--怎样添加其他老师--怎样添加课程助教--如何添加课程助教--如何添加其他老师--添加其他老师到课程--添加其他助教到课程', '', 0, 0, 1544249896),
(5, '如何添加活动', '老师您好！有以下一些步骤：\n一、“打开编辑功能”的方法\n方法一：快捷入口。快捷入口位于页面的右上角。点击快捷入口，点击“打开编辑功能“。\n方法二：侧边导航【系统管理】-【课程管理】-“打开编辑功能”。\n\n二、添加一个活动或资源\n１、在相应主题的右下角，点击“添加一个活动或资源”。\n２、在弹框中，选择相应的活动／资源。以“网页”为例。\n３、在跳转的活动设置页中，填写活动名称以及相关内容。\n４、最后，在页面底部左方，点击“保存并返回课程”回到课程页面继续添加活动，或者“保存并预览”查看活动效果。\n\n具体过程可', '怎样添加活动--添加活动', '测试', 0, 0, 1544249896),
(6, '课程小组有多少种情况', '老师您好！课程小组有三种\n\n1、你是一门课程的老师，该课程有几个班级的学生，你想一次只看一个班级的活动和成绩册。\n\n2、你是一位老师，与其他老师共享一门课程，并且你想过滤你的班级的活动和成绩簿，不想看到你同事班上的学生。\n\n3、你希望将特定的活动，资源或主题部分分配给一个班级或一组用户，并且你不希望其他人看到它。', '课程小组有哪些--什么是课程小组--课程小组', '', 0, 26, 1544249896),
(7, '怎样设置小组级别', '老师您好！课程级别定义的小组模式是该课程中所有活动的默认模式。要使用课程级别的小组模式，在【课程管理】&amp;amp;amp;gt; 【更改设置】中设置组模式。一般不使用。', '如何设置课程小组级别--怎样设置课程小组级别--课程小组级别', '', 0, 0, 1544249896),
(8, '什么是小组模式', '老师您好！小组模式分为三种\n1、无小组：没有小组。\n\n2、分隔小组：每个小组只能看到自己的小组，其他小组则不可见。\n\n3、可视小组：每个小组都在自己的小组中工作，但也可以看到其他小组。 （其他小组的作品是只读的。）', '小组模式有多少种--小组模式', '', 0, 1, 1544249896),
(9, '怎样创建小组', '老师您好！具体过程请查看文档：http://moodle.scnu.edu.cn/mod/page/view.php?id=28346#a4', '如何新建小组--怎样新建小组--新建小组', '', 0, 1, 1544249896),
(10, '如何设置课程选课方式', '老师您好！点击视频http://moodle.scnu.edu.cn/mod/page/view.php?id=28347', '设置课程选课方式--怎样设置课程选课方式--学生如何选课--学生如何加入到课程--如何设置选课密码--怎样设置选课密码--怎样让学生加入到课程', '', 0, 0, 1544249896),
(11, '有教学文档吗', '老师您好！有的。请点击地址进行学习http://moodle.scnu.edu.cn/course/view.php?id=5', '你们能提供网上教学资源吗？--有教学资源吗？--教学资源--视频教学--有视频教学资源吗', '', 0, 0, 1544249896),
(12, '在线课程设计是什么', '老师您好！这里是砺儒云课堂在线课程设计的主要内容地址：http://moodle.scnu.edu.cn/course/view.php?id=5&amp;amp;section=3', '在线课程设计有哪些--在线课程设计', '', 0, 0, 1544249896),
(13, '标签及图书的使用方法', '老师您好！标签的内容可以直接显示在课程的界面，可以呈现课程内容简介，美化课程界面等，使得内容更加清晰。比如黄老师在砺儒云课堂的平台上建立了自己的课程——《华师简介》，她希望呈现的课程内容清晰，学生能一目了然看到本课程的简介。这是关于标签及图书的视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28358\n\n这是标签的使用图文教程：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6951', '', NULL, NULL, 0, 1544249896),
(14, '网页及网页地址', '老师您好！这是关于网页及网页地址的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28359', '', NULL, NULL, 0, 1544249896),
(15, '文件及文件夹的使用方法', '老师您好！由于文件与文件夹的使用流程基本相同，所以在这里以添加文件夹为例。\n1.打开编辑功能，在相应的活动版块中添加“文件夹”。\n2.对该文件夹进行相关的设置，【概要】-【描述】最好也填写。在【内容】-【文件】中进行文件和文件夹的添加时，在所有当前版本的Chrome，Firefox 和 Safari，以及 Internet Explorer v10 以上都可以使用拖拽的方式同时上传一个或多个文件。在【限制访问】-【添加限制】中可以限制学生在完成其他活动或在指定时间等条件访问本文件夹。\n3.保存并返回课程。', '', '', 0, 1, 1544249896),
(16, 'Word批量题目导入题库', '老师您好！老师们在创建题库时，可将题目整合到固定格式的excel文档中，再利用excel插件进行批量导入。这是题库的视频教程。http://moodle.scnu.edu.cn/mod/page/view.php?id=28361', '', '', NULL, 0, 1544249896),
(17, '上传与引用LearnTV视频的方法', '老师您好！这是LearnTV视频的使用教程。http://moodle.scnu.edu.cn/mod/page/view.php?id=28363', '', '', NULL, 0, 1544249896),
(18, '投票的使用方法', '老师您好！这是投票的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28364', '', '', NULL, 0, 1544249896),
(19, '作业的使用方法', '老师您好！1.进入到自己需要添加作业的课程页面中，点击课程页面中右上方的“打开编辑功能”。\n2.在自己需要添加作业的主题当中，点击主题栏右下方的“添加一个活动或资源”。 \n3.寻找到“作业”并选中，再点击添加。\n4.概要栏目中的设置相对应在主题界面所显示的内容。\n5.点击保存，即可完成作业的添加与设置。\n\n这是关于作业的视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28365\n\n这是关于作业的图文教程：http://moodle.scnu.', '', NULL, NULL, 0, 1544249896),
(20, '问卷调查的使用方法', '老师您好！问卷有以下一些使用步骤：\n1.“打开编辑功能”——“添加一个活动或资源”——“问卷调查”；\n2. 其他信息与添加“测试”一样，要先填写简单的信息介绍，保存之后，再次点击才能进行内容的编辑。注意只有小框里打勾了，才能对该栏目进行编辑。\n3. 选择“添加新问题”，然后根据问卷需求选择单选还是多选等\n4. 点击“预览”就可以查看问题的最终版，也可以在“问题”一栏中继续添加问题。\n\n问卷调查的视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=', '调查问卷的使用方法|怎样使用调查问卷|如何使用调查问卷|||||||', NULL, NULL, 0, 1544249896),
(21, '程序教学的使用方法', '老师您好！这是您要的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28367', '', '', 0, 0, 1544249896),
(22, '聊天室的使用方法', '老师您好！聊天的使用有一些步骤哦：\n1.打开编辑功能，在相应的活动版块中添加【聊天】。【聊天】可以在线上学习时用于小组或全班一起在聊天室中进行讨论，有助于增进了解、深入话题。与【讨论区】的区别是【聊天】是进行实时的、同步的讨论。\n2.对该文件夹进行相关的设置\n3.保存并返回课程。\n\n这是聊天室的使用视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28368', '', NULL, NULL, 0, 1544249896),
(23, '讨论区的使用方法', '老师您好！这是讨论区的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28369\n\n这是讨论区的图文解释地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6955', '', NULL, NULL, 0, 1544249896),
(24, '如何添加在线测验', '老师您好！添加在线测验有以下一些步骤：\n1.打开编辑功能，在相应的活动版块中添加“测验”。\n2.对该文件夹进行相关的设置\n3.保存并返回课程。\n\n这是使用教程。http://moodle.scnu.edu.cn/mod/page/view.php?id=28370', '', NULL, NULL, 0, 1544249896),
(25, '词汇表的使用方法', '老师您好！这是词汇表的使用视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28372\n\n这是词汇表的图文教程地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6956', '', NULL, NULL, 0, 1544249896),
(26, '怎么进行团队构建', '老师您好！团队构建活动是根据学生回答教师设计的问卷题目的答案，将学生分配到不同的小组（或大组的小组中）的工具。主要运用的情景是：\n\n情形1：线下组队，线上分组\n1.打开编辑功能，点击“添加一个活动或资源”，添加团队构建活动。\n2.在创建页面中，填写活动名称，设置问卷开放时间和结束时间。\n3.在设置问卷调查页面，添加题目。支持的题目类型：单选题、多选题（非空）、多选课（可空）。\n4.添加完所有题目之后，点击保存问卷。\n5.当问卷开放，学生填写完问卷后，一个小组一个小组地进行添加。\n6.在点击“构建团队”后，', '', NULL, NULL, 1, 1544249896),
(27, '如何添加版块内容', '老师您好！在moodle平台中，老师们可在所编辑课程里面添加版块，方便课程管理。\n方法：进入所编辑课程页面，打开编辑功能，拉至页面左下方位置，选择“添加”。这是添加版块内容的视频教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28374', '', NULL, NULL, 0, 1544249896),
(28, '如何为课程添加“课程动态”模块', '老师您好！这是为课程添加“课程动态”模块的使用教程。http://moodle.scnu.edu.cn/mod/url/view.php?id=28375', '', NULL, NULL, 0, 1544249896),
(29, '如何使用日历', '老师您好！砺儒云课堂中的日历可以方便我们的课程管理，设置的作业、测验、出席的截止时间会自动显示在日历上，老师们也可以利用日历设置用户新事件的提醒。这是日历的使用教程，希望能帮到您。http://moodle.scnu.edu.cn/mod/page/view.php?id=28376', '', NULL, NULL, 0, 1544249896),
(30, '如何设置即将到来的事件', '老师您好！这是设置即将到来的事件教程。http://moodle.scnu.edu.cn/mod/url/view.php?id=28377', '', NULL, NULL, 0, 1544249896),
(31, '如何设置报表', '老师您好！这是设置报表的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28378', '', NULL, NULL, 0, 1544249896),
(32, '如何使用成绩模块', '老师您好！这是成绩模块的使用教程。http://moodle.scnu.edu.cn/mod/resource/view.php?id=28379', '', '', 16, 0, 1544249896),
(33, '标签的使用', '老师您好！标签的内容可以直接显示在课程的界面，可以呈现课程内容简介，美化课程界面等，使得内容更加清晰。标签的添加步骤：\n1.登陆平台，进入自己开设的课程，并点击右上角打开课程编辑功能\n2.在需要添加标签的主题中，比如在“学校概况”这一主题下，点击【添加一个活动或资源】，得到下拉列表，选择“标签”。即可完成标签的添加。\n', '', NULL, NULL, 0, 1544249896),
(34, '如何给一组图片添加超链接', '老师您好！在任何一个文本编辑器，找到【内嵌文件】（编辑器工具栏第一栏最右侧按钮），上传PDF文件（或者图片等）后，关闭弹框。\n\n在文本编辑器中输入文件名称或者其他，选中文字，添加超链接。（如同添加PPT超链接）\n\n在添加超链接时使用【预览】，选中【内嵌文件】中的文件，选中打开方式，插入即可。\n\n最后页面提交保存后（如发表帖子等），点击该链接就能在线观看pdf。\n\n详细的图文教程请点击：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6968', '', NULL, NULL, 0, 1544249896),
(35, '如何导入Word文档内容', '老师您好！“导入word文本内容”能将.docx文件内容导入成网页内容，包括标题、列表以及文字样式和图片。导入的步骤有这些：\n①、新增的文本编辑器（Atto）与现用的文本编辑器（TinyMCE）不同，增加了“导入word文本内容”按钮。所以我们首先得切换文本编辑器！\n②、进入【使用偏好】\n③、进入“编辑器选项”\n④、选择“Atto HTML编辑器”，并保存更改\n⑤、以添加“网页”为例，输入网页名称后，在页面内容的文本编辑框中点击“导入word文档内容”按钮\n⑥、上传一个.docx后缀的文档！\n⑦、保存\n详', '', NULL, NULL, 0, 1544249896),
(36, '如何在程序教学中选择题选项顺序的随机性', '老师您好！程序教学中选择题的选项，在学生每一次作答时，系统都会随机排序，当学生在重新回答某个固定的第X个答案，结果都可能是错误的。因此程序教学选择题中选项顺序的随机性是具有防作弊的功能，并且让能够学生真正学到知识。图文地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6966', '', NULL, NULL, 0, 1544249896),
(37, '板块的两种页面呈现方式', '老师您好！在Moodle 平台里面板块的显示方式是有两种，一种是停靠栏形式，另一种是主页面的形式。具体的图文教程地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6965', '', NULL, NULL, 0, 1544249896),
(38, '报表中的Content accesses是什么', '老师您好！Content accesses里面可以显示的活动或资源有【文件】、【网页】、【网页地址】。查看具体的图文教程请点击：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6964', '', NULL, NULL, 5, 1544249896),
(39, '如何更改最大文件上传大小', '老师您好！在课程主页面左下方“课程管理”进入“更改设置”\n\n进入页面之后，可以更改文件上传大小，有10kb~2GB不等，如图所示。修改之后，整个课程的所有活动的文件上传大小都会以此标准规定。\n\n具体的图文教程请点击地址查看：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6961', '', NULL, NULL, 0, 1544249896),
(40, '程序教学中的簇是什么', '老师您好！在程序教学中，学生进入簇页面时，需要回答任意一个之前在程序教学中设置的问题，并且只有答对了才能跳出簇。簇可以有效地巩固学生的知识。', '', NULL, NULL, 0, 1544249896),
(41, '程序教学中的题目页是什么', '老师您好！在程序教学中，题目页相当于测验，它可以有效地帮助教师、以及学生自身，了解学生们在程序教学活动中的掌握情况。详细的参数设置请查看：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6958', '', NULL, NULL, 0, 1544249896),
(42, '怎样添加词条', '老师您好！这是添加词条的方法地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6957', '', NULL, NULL, 0, 1544249896),
(43, '程序教学内容页怎样建立', '老师您好！程序教学中的内容页是程序教学里面的重要组成部分，所有内容页能够实现像PPT里面的超链接的功能，不过在程序教学里面是关于页面跳转的设置。这里是具体的图文教程地址：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6954', '', NULL, NULL, 0, 1544249896),
(44, '程序教学活动是什么', '老师您好！程序教学设计包括内容页面、问题页面、分支表。\n\n【内容页面】：呈现与知识点相关的内容；\n\n【问题页面】：学生回答相应的问题然后得到反馈；\n\n【分支表】：为同学提供选择的机会。\n\n各个页面之间可以相互跳转，教师在进行程序教学活动中要制定好详细的流程。例如：学习内容、每一页可以展示的内容，学生回答问题之后获得的反馈，每一个页面之间是如何跳转的。\n\n具体的图文教程请点击：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6952', '', NULL, NULL, 0, 1544249896),
(45, '互动评价的使用流程', '老师您好！评价有以下一些步骤哦！\n1.打开编辑功能，添加“互动评价”活动。\n2.对该互动评价进行相关的设置。【概要】-【描述】、【评价设置】-【如何评价】、【作业设置】-【作业说明】该三处最好也填写。\n3.保存并返回课程后，点击该活动，进入设置界面。一开始正处于设置阶段。点击“修改评价表格”。\n4.在“评价表格”中进行相关的设置并保存。\n设置阶段准备完毕后切换到下一状态【提交作业阶段】，在此状态写学生需要提交对自己的文字评价（作业）。\n6.当所有学生评价自己（提交作业）就可以给他们指派互评的任务，点击“指', '', NULL, NULL, 1, 1544249896),
(46, '备份的使用流程', '老师您好！\n1.点击“系统管理”版块“课程管理”中的“备份”。\n2.选择自己想要备份的部分，例如：活动和资源、版块、过滤器和日历时间等（默认全选）。部分内容是无法备份的。设置完之后点击右下角下一步。\n3.选择具体备份活动和资源、版块、过滤器和日历时间等中的某几项。\n还可点击最上方的“显示类型选项”，选择是否备份“活动和资源”中的各项具体内容。\n4.更改备份文件名，并确认“备份设置”以及“包括项目”。确认无误后，点击右下角“开始备份”。\n5.待显示成功建立备份文件后即可。\n\n这是备份与恢复的使用流程：htt', '', NULL, NULL, 0, 1544249896),
(47, '多项选择题的添加方法', '老师您好！这是多选题的图文教程，请认真学习：http://moodle.scnu.edu.cn/mod/forum/discuss.php?d=6944', '', NULL, NULL, 0, 1544249896),
(48, '学生提交作业的格式有哪些', '老师老师您好！学生提交作业时，最好以PDF格式的文档提交到平台。这样可以方便老师们在线批改作业。批改作业的流程如下：\n1. 点击进入相应的“作业”活动\n2. 进入下一个界面，并点击“成绩”\n3. 进入界面，即可清楚看到学生的个人信息及作业，同时教师可以利用工具进行批改作业，并且给作业评分。\n4. 批改完后，可以选中“通知学生”，学生即可收到教师的作业反馈。并保存更改，第一份作业的批改就完成啦。\n5. 点击右上角的变更用户的左右两个蓝色的按钮，就可以查看其他学生的作业了\n\n这是学生提交作业格式的具体介绍和批', '怎样批改作业', NULL, NULL, 0, 1544249896),
(49, '视频转码压制软件的使用', '老师您好！今天给大家推荐一款可以帮助老师们压缩视频且转码成mp4格式的视频软件：视频转码压制软件，该软件的具体操作方式如下：\n\n1.解压视频转码压制软件至文件夹\n\n2.打开文件夹，找到文件：“请把视频拖拽至此处压制视频(fast)”\n\n3.将所要压缩编码的视频拖拽到这个windows批处理文件中，即可开始视频的压缩与转码\n\n4.待文件转码完成后，可在源视频所在文件夹中找到压缩处理后的视频，此时的视频格式为mp4格式，可供学生在线观看。\n\n具体流程见：http://moodle.scnu.edu.cn/mo', '', NULL, NULL, 0, 1544249896),
(139, '以组为单位查看每个学生的作业', '老师您好！添加作业时，分组提交的选项可以让学生以组为单位提交作业。但分组提交只能交一份作业，“分组提交设置”中的“是否要求每个组成员提交”也只是让每个组员都需要点击“提交”按钮，确定作业是否为最终版本而已。如果教师想以组为单位查看每个学生的作业，可以根据以下的设置实现。\n1.在作业的“作业类型”设置中，“最大的文件上传数量”增加至跟小组成员数量一样。\n2.“提交设置”中，“学生必须点击提交按钮”选择“是”：\n3.在“分组提交设置”中，“是否要求每个组成员提交”选择“是”：', '', NULL, NULL, 0, 1544403153),
(138, '班级通讯录的设置', '老师您好！在moodle平台上，我们可设置一个班级通讯录，方便大家联系、交流。设置方法：\n1.添加一个活动或资源，选择数据库 \n2.将数据库命名为“班级通讯录”并增加相关描述 \n3.在“字段”处选择“文本输入”\n4.添加三个字段，分别命名为“姓名”“学号”“联系方式”\n5.选择“添加字段”，进行通讯录的添加\n6.选择“模板”>“独立模板”，依次点击左侧的“姓名”“学号”“联系方式”，直至右端出现对应数据；\n7. 选择保存模板并进入“显示列表”查看。', '', NULL, NULL, 0, 1544403153),
(137, '视频的上传方式有哪些？', '老师您好！在moodle平台上，上传视频有两种方法：\n第一种：在“描述”处直接上传视频，这种方法上传的视频可在网站上直接观看；\n第二种：在“选择文件”处以附件形式上传，这种方式上传的视频需下载观看。', '', NULL, NULL, 0, 1544403153),
(136, '恢复的使用流程', '老师您好！1.点击“系统管理”版块“课程管理”中的“恢复”。\n2.直接在“用户私人备份区”点击需要恢复的课程后方的“恢复”。或者将已有的备份文件上传导入后点击“恢复”。\n3.确认“备份细节”“备份设置”“课程细节”“课程小结”等内容。确认无误后点击最下方右侧“继续”。\n4.选择“合并备份课程到此课程”或“删除课程内容后恢复”。\n5.选择“恢复设置”（一般为默认全部）确认完毕后点击右下方下一步。\n6.更改“课程设置”\n7.确认“备份设置”和“课程设置”，确认无误后点击右下方“开始恢复”。\n8.待显示“课程已', '', NULL, NULL, 0, 1544403153),
(135, '怎样创建网页', '老师您好！教师可以自行创建网页，以网页的形式展现学习资源。网页的添加步骤：\n1. 登陆平台，进入自己开设的课程，并点击右上角打开课程编辑功能\n\n2. 在需要添加网页的主题中，比如在“学校概况”这一主题下，点击【添加一个活动或资源】，得到下拉列表，选择“网页”。即可完成网页的添加。\n\n', '', NULL, NULL, 0, 1544403153),
(134, '怎样保持选课时长', '老师您好！关于自助选课设置中的“保持选课时长”，指的是选了课的学生的身份有效期，而不是选课时间哦！', '', NULL, NULL, 0, 1544403153),
(133, '课程怎样设置', '老师您好！在moodle平台上，老师们可以对课程进行一些设置，如：“课程是否可见”、“课程开始时间”、“小组设置”以及“课程标签”等；\n方法：进入所编辑课程页面，打开“系统管理”> “课程管理”> “更改设置”', '', NULL, NULL, 0, 1544403153),
(132, '使用“出席”功能进行扫码签到', '老师您好！砺儒云课堂提供了一项使用二维码扫码签到的功能，方便老师在进行教学的过程中进行考勤，下面我们简单介绍该项功能是使用方法：\n1.点击【打开编辑功能】，并点击【添加一个活动或资源】，选择【出席】并确认添加该活动\n2、根据教学实际要求填写相应设置，接着点击【保存并预览】。\n3、在页面上方点击【新增上课时间】，根据教学实际情况填写响应信息，并且不能勾选【允许学生登记自己的出席情况】选项，接着点击【添加】按钮。\n4、在页面上方已经显示成功建立一个上课时间，教师在需要考勤签到时，点击【出席】这个活动并点击如下', '', NULL, NULL, 0, 1544403153),
(131, '怎样使用wiki', '老师您好！Wiki与其他活动和资源的最大不同之处就是，可以允许每一个参与到学习的人续写和链接当前文本的内容，从而共同创建内容。步骤如下：\n1.新建页面，可以选择三种不同的样式\n2.编辑界面，编辑完成后点击保存即可。\n3.下一个同学可以在上一个同学的基础上编辑界面。\n4.可以在“历史”的选项中查看同学们的编辑历史。\n', '', NULL, NULL, 0, 1544403153),
(130, 'wiki是什么', '老师您好！砺儒云课堂平台中的wiki是一个强大的协作工具，全班同学可以一起编辑一个文本，创造一个班级成果，或者每个学生都拥有自己的wiki并且和同学协作。wiki的使用方法如下：\n1.打开“课程编辑功能”，在“添加一个活动或资源”中选择添加“wiki协作”。\n2.进入设置界面，进行相关的设置。\n3.设置完成后，点击“保存并返回课程”，即可在课程首页上找到新建的wiki项目。\n', '怎样创建wiki|如何创建wiki|如何新建wiki', NULL, NULL, 0, 1544403153),
(128, '证书的设置方法', '老师您好！证书模块允许教师在课程中动态生成证书。\n1、首先【打开编辑功能】→【添加一个活动或资源】，选择【证书】模块并点击【添加】。\n2、命名证书名称\n3、设置颁发选项\n4、设置文本选项\n5、设置设计选项\n6、设置通用模块\n7、添加访问限制\n8、点击【保存并返回课程】完成设置。\n9、学生可点击这个活动获取证书。', '', NULL, NULL, 0, 1544403153),
(129, '测验的使用流程', '老师您好！测验的流程步骤有这下：\n1.打开编辑功能，在相应的活动版块中添加“测验”。\n2.对该文件夹进行相关的设置，【概要】-【描述】最好也填写。\n3.保存好之后即可返回课程。', '', NULL, NULL, 0, 1544403153),
(62, '怎样使用日志', '老师您好！砺儒云课堂平台中“量表”这一功能的使用可以帮助老师对参与课程学习同学的学习情况，在线时长和在线活动进行实时的了解。“日志”便记录了课程参与人员在课程中的具体活动，下面为大家介绍具体的使用方法：\n1、进入开设的课程，在“系统管理”菜单中选择“报表”\n2、选择“日志”\n3、在设置界面中，根据自己的需要进行设置：\n4、获取日志后，将页面拉至最底部，点击“下载”可对日志进行下载', '', NULL, NULL, 0, 1544249896),
(63, '怎样使用课程活动', '老师您好！砺儒云课堂平台中“量表”这一功能的使用可以帮助老师对参与课程学习同学的学习情况，在线时长和在线活动进行实时的了解。其中“课程活动”这一功能课协助老师了解在开设课程中每个活动的参与情况和访问量，下面为大家介绍具体的使用方法：\n1、进入开设的课程，在“系统管理”菜单中选择“报表”\n2、选择“课程活动”\n3、即可查看', '', NULL, NULL, 0, 1544249896),
(64, '如何使用活动进度', '老师您好！砺儒云课堂平台中“量表”这一功能的使用可以帮助老师对参与课程学习同学的学习情况，在线时长和在线活动进行实时的了解。其中“活动进度”这一功能协助老师了解在开设课程中每个活动学生的完成进度，便于老师安排教学活动，下面为大家介绍具体的使用方法：\n1、进入开设的课程，在“系统管理”菜单中选择“报表”\n2、选择“活动进度”\n3、即可查看：\n4、可根据需要进行下载', '', NULL, NULL, 0, 1544249896),
(65, '不固定顺序填空题的相关设置', '老师您好！设置填空题时，若填空的顺序不固定，可以通过以下的设置更加灵活地批改学生的答案。\n1.假使设置一道填空题，其答案可以是“零、正整数”、“正整数、零”\n2.在答案1、答案2中分别填写“零*正整数”、“正整数*零”，并把两个答案的成绩都设为100%（“*”表示可任意替代的字符）\n3.如果回答的顺序不一样，依然会显示答案正确', '', NULL, NULL, 0, 1544249896),
(66, '随机题的添加', '老师您好！在moodle课程中，可以实现从题库中随机抽取试题让学生进行完成。具体步骤如下：\n（1）在给定的Excel工作表中加入要让学生抽取的所有题目\n（2）在所要操作的课程中选择“题库——类别”\n（3）新建一个题库类别\n（4）导入章节题目\n（5）返回到课程页面，在对应章节处新建一个测验，并进入测验编辑\n（6）添加随机题\n（7）保存即可实现随即题的测验。', '', NULL, NULL, 0, 1544249896),
(67, '输入选课密码进入课程的学生为什么身份会是教师？', '您好！要看看你们设的密码是不是一样。教师的选课有密码，学生的选课没有密码。教师的选课使用的小组密码。', '', NULL, NULL, 1, 1544249896),
(68, '如何在主题下做出分类', '老师您好！添加标签就可以了。', '', NULL, NULL, 0, 1544249896),
(69, '对一个章节做知识要点解析，按照一级标题、二级标题的知识结构构建，该添加什么活动？', '老师您好！需要上传PDF文件进行解析。', '', NULL, NULL, 0, 1544249896),
(70, '如何添加作业？', '老师您好！打开编辑模式，点击“添加一个活动或资源”，选择“作业”进行设置即可。', '', NULL, NULL, 0, 1544249896),
(71, '作业能否重新提交？', '老师您好！可以，可以设置重新提交次数', '', NULL, NULL, 0, 1544249896),
(73, '是否可以先对于学生隐藏上交了的作业，等答案公布再开放', '老师您好！不可以。作业里其他人是看不到别人作业的。建议使用互动评价，设置有效性。', '', NULL, NULL, 1, 1544249896),
(74, '同一课程有四位老师，分设了四个班，那么老师甲布置的作业是不是只有甲班的同学能够看到？', '老师您好！不是，所有选了课的同学都能看到。想要实现该效果可以设置每个活动访问限制。', '', '测试', 0, 0, 1544249896),
(75, '能否在画面呈现所有提交的作业，全部改完再一起保存评分结果？', '老师您好！使用快速评分模式即可', '', NULL, NULL, 1, 1544249896),
(76, '使用快速评分模式时如何避免学生在批改过程中改动作业？', '老师您好！选择“编辑”下拉列表中的“禁止更改作业”或者选中作业将其锁定', '', NULL, NULL, 0, 1544249896),
(77, '学生上交的作业在哪里批改成绩？', '老师您好！进入作业页面，点击下方“成绩”按键，进入作业批改界面。', '', NULL, NULL, 0, 1544249896),
(78, '为什么我没办法设置其他人为助教？', '老师您好！助教本身是不可以设置助教的，只有教师或者管理员等高于助教权限的角色才可以设置助教。', '', NULL, NULL, 0, 1544249896),
(79, '如何针对某个组进行考勤？', '老师您好！在“限制访问”里直接勾选那个组。', '', NULL, NULL, 0, 1544249896),
(80, '学生上交的音频作业能否在线听？', '老师您好！不能的，我们平台暂时做不到', '', NULL, NULL, 1, 1544249896),
(81, '访客能否看到聊天室内容', '老师您好！这个是不可以的，访客限制较多，没有选课密码一般不能观看', '', NULL, NULL, 0, 1544249896),
(83, '临近期末了，想把这个课程“关”了，不对学生开放，如何做？', '老师您好！在课程设置中对课程可见性选择隐藏。', '', NULL, NULL, 0, 1544249896),
(84, '如何再次开放课程？', '老师您好！在“更改设置”中更改“课程结束时间”即可', '', NULL, NULL, 0, 1544249896),
(85, '如何设置开放课程', '老师您好！先开启自助选课选项，选择不设置选课密码。', '', NULL, NULL, 0, 1544249896),
(86, '上传文档内容有没有复制粘贴功能？', '老师您好！没有，建议添加”文件“或”文件夹“资源，将文档上传，或者参考群文件中的“导入Word文档内容”', '', NULL, NULL, 0, 1544249896),
(87, '如何添加文件或文件夹活动资源？', '老师您好！进入编辑状态，点击“添加一个活动或资源”，在最下方找到文件或文件夹，进入活动设置。', '', NULL, NULL, 0, 1544249896),
(88, '子文件夹如何重新排序？', '老师您好！在文件夹前编序号1，2，3....自动识别按数字排序', '', NULL, NULL, 0, 1544249896),
(89, '老师想要把文件拖进文件夹里', '老师您好！添加了文件夹后添加文件的时候选择服务器文件就能够看到之前上传的资料', '', NULL, NULL, 0, 1544249896),
(90, '讨论区回帖中可以上传jpg格式照片吗？在哪里上传？', '老师您好！可以，在附件处上传即可', '', NULL, NULL, 0, 1544249896),
(91, '学生无法在新闻讨论区回复？', '老师您好！只有教师和管理员能在新闻讨论区回复发帖。', '', NULL, NULL, 0, 1544249896),
(92, '讨论区的话题冻结了，是否能重开？', '老师您好！进入讨论区后，点击进入话题——点选话题中发言人右下角的“编辑”——在“可视时段”中设置“结束时间”。\n\n建议直接不勾选结束时间，否则冻结后学生将无法查看讨论情况。', '', NULL, NULL, 0, 1544249896),
(93, '老师找不到设置讨论组的小组模式？', '老师您好！在课程讨论区的通用模块内，将小组模式改为可视小组即可。', '', NULL, NULL, 0, 1544249896),
(94, '讨论组能不能设置小组讨论？', '老师您好！可以，设置为可视小组可以进行', '', NULL, NULL, 0, 1544249896),
(95, '如何使平台中的视频全屏？', '老师您好！在HTML编码器中将最后一行的”width="900""height=600"变为"width=900""height=600"就可以了！！！', '', NULL, NULL, 0, 1544249896),
(96, '请问我最后汇总作业，小测的分数，可以生成一份成绩单吗？', '老师您好！可以设置成绩册，主要包括成绩项的类别划分及各成绩项的权重设置。', '', NULL, NULL, 0, 1544249896),
(97, '如何更改学生的组别？', '老师您好！注意了，要先将学生与本来所在的组别踢出', '', NULL, NULL, 0, 1544249896),
(98, '为什么课程的图片突然显示不出来       ', '老师您好！可能原因：ie版本过低，还有可能是浏览器将图片拦截了或者看图片的控件没有解决方法:更新或者更换浏览器尝试，猎豹浏览器请使用极速模式....', '', NULL, NULL, 0, 1544249896),
(99, 'learn TV的视频出错要重传', '老师您好！可以通过ADD新视频来重传', '', NULL, NULL, 0, 1544249896),
(100, '程序教学是什么？', '老师您好！程序教学模块使教师为学生创造自适应性的学习体验，它有由一系列的页面组成，每个页面可以包含题目', '', NULL, NULL, 0, 1544249896),
(101, '如何进行课程直播？', '老师您好！可以在learn TV中发起直播，发起直播后，将直播链接发送给学生即可。', '', NULL, NULL, 0, 1544249896),
(102, '如何在励儒云添加静态学习网站，就是html格式的网页？', '老师您好！添加网页或网页地址活动，并添加网址。', '', NULL, NULL, 0, 1544249896),
(103, '要怎么查看已经设置好的选课密码？', '老师您好！在设置密码时，点击后面的“放大镜”按钮即可看到密码', '', NULL, NULL, 0, 1544249896),
(104, '新闻发布区可以上传视频吗？', '老师您好！可以，在文件处上传视频。', '', NULL, NULL, 0, 1544249896),
(105, 'word中的公式无法导入', '老师您好！平台暂时无法支持word中的公式，只能重新输入', '', NULL, NULL, 0, 1544249896),
(106, '填空题两个答案之间怎么处理？', '老师您好！可以用*来代替任意字符。例子：就是说在答案内输入“空1*空2”，学生在两个空之间无论填“，”还是空格，系统都会自动识别的', '', NULL, NULL, 0, 1544249896),
(107, '如何将到导入题库的题目分类？', '老师您好！在将题目导入题库的时候，可以新建一个文件夹，将题目导入文件夹中', '', NULL, NULL, 0, 1544249896),
(108, '调查问卷如何设置多选题', '老师您好！在下拉选框中选择复选框', '', NULL, NULL, 0, 1544249896),
(109, '学生反映登录砺儒云做测验时做了十几分钟就自动退出去了？', '老师您好！这是因为在编辑测验时设置了时间限制，当期限终止时自动提交了。', '', NULL, NULL, 0, 1544249896),
(110, '题目名称和题干有什么区别？', '老师您好！题目名称是方便老师识别题目，但不会显示给学生看，而题干是给学生看的。', '', NULL, NULL, 0, 1544249896),
(111, '测验无法显示已提交', '老师您好！在测验的时间安排那里需要设置，否则无法自动提交，会导致学生的作业虽然已经做了，但是没提交', '', NULL, NULL, 0, 1544249896),
(112, '如何对一项活动进行时间上的限制访问？', '老师您好！在设置时选择“限制访问”中的“日期“进行设置，注意“从”“和”直到“两个关键词的区别。', '', NULL, NULL, 0, 1544249896),
(113, '成绩册可以下载打印吗？', '老师您好！可以，在成绩选择导出方式后打印。', '', NULL, NULL, 0, 1544249896),
(114, '有学生参加过的测验是否可以修改', '老师您好！不可以的', '', NULL, NULL, 0, 1544249896),
(115, '如何导入题库？', '老师您好！QQ上有导入格式，可以在上面编辑，选择从excel导入', '', NULL, NULL, 0, 1544249896),
(116, '活动主题可以移动顺序吗？', '老师您好！可以的。', '', NULL, NULL, 0, 1544249896),
(117, '请问上传了授课视频之后，可以点击哪里设置，将视频改为访客点击之后出现视频播放页面而不是出现下载对话框呢？', '老师您好！在“描述”处直接上传视频即可', '', NULL, NULL, 0, 1544249896),
(118, '请问已经上传的视频有没有办法把它们变成在线观看？还是要重新上传？', '老师您好！要重新上传了', '', NULL, NULL, 0, 1544249896),
(119, '请问视频上传格式及方法？', '老师您好！视频上传格式最佳为mp4格式，编码方式为h.264,视频过大先压缩。', '', NULL, NULL, 0, 1544249896),
(120, '如何使用同伴互评量规？', '老师您好！在成绩设置那里评分策略使用量规，保存返回课程后进行设置量规等级。', '', NULL, NULL, 0, 1544249896),
(121, '范例作业如何上传问题？', '老师您好！同样在互动评价那里设置使用范例作业，保存并返回课程之后添加范例作业，直接拖文件到相应的位置即可。', '', NULL, NULL, 0, 1544249896),
(122, '在互动评价里能否设置老师评和学生评两者结合', '老师您好！不可以的', '', NULL, NULL, 0, 1544249896),
(123, '请问互评模块，学生已经提交作业了，下一阶段是指派任务，学生较多，能不能实现学生自行选择自己想去要评价的作业进行评价？', '老师您好！在“指派互评任务”中选择“随机分配”，由系统随机分配给学生评价作业。', '', NULL, NULL, 0, 1544249896),
(124, '如何让外教老师登陆使用云平台', '老师您好，有以下的步骤哦：\n \n①.让外教老师提供：邮箱、姓名、账号及密码（将用来登陆平台）；\n\n②.将相关信息发送给管理员，管理员将会帮助老师创建账号；\n\n③.创建账号后，外教即可通过平台本地用户登陆使用云平台', '', NULL, NULL, 0, 1544249896),
(125, '如何分组布置作业？', '老师您好！您可以设置多个“作业”，让不同小组的同学通过不同的“作业”进行提交', '', NULL, NULL, 0, 1544249896),
(126, '在课程的上传视频只能限制在100M内了吗？', '老师您好，视频可以进行压缩之后上传', '', '', NULL, 0, 1544249896),
(127, '学生每次提交作业后不能再修改了，一定要老师退回为草稿，怎么才能让学生自己可以多次提交并覆盖掉前面提交的作业？', '老师，您要在作业的提交设置中将重试开启打开', '', NULL, NULL, 1, 1544249896),
(140, '你好呀', '你好', '', NULL, NULL, 8, 1544615936),
(141, '你好', '你好', '', NULL, NULL, 0, 1544615936),
(143, 'fffff', '1', '', '', 0, 2, 1544689336),
(145, 'v', 'b', '', '', 0, 0, 1544689594),
(1561, '2', '测试', '', NULL, 116, 0, 1546849018),
(155, '987', '987', '987--123', '', 0, 0, 1544748723),
(1552, '0', '0', '', '', 108, 0, 1545631693),
(1559, '这是测试问题', '测试', '', '', 16, 0, 1545751716),
(1536, '测试测试', '这是测试问题', '测试', '测试', 0, 0, 1545183744),
(1558, '这是测试问题？', '这是测试问题', '测试问题', '测试', 16, 0, 1545651005),
(1564, '一加一等于2？', '你真聪明！', '', '', 108, 1, 1547172100),
(1566, '邱琦雯驱蚊器额外全额完全企鹅', '你在说什么鬼', '', '', 0, 0, 1547582614),
(1567, '321?', '123', '', '', 0, NULL, 1547600782),
(1568, '啊啊', '您说什么？', '啊', '', 16, 2, 1547600950),
(1569, '7887', '1718', '78--87', '测试', 16, NULL, 1547601740),
(1570, '7894', '8749', '', NULL, 108, 1, 1547603049);

-- --------------------------------------------------------

--
-- 表的结构 `quickreplylibrary`
--

CREATE TABLE `quickreplylibrary` (
  `Q_ID` int(11) NOT NULL COMMENT '自增ID',
  `Q_classification` varchar(16) NOT NULL COMMENT '快捷回复分类',
  `Q_Belongs` int(11) NOT NULL COMMENT '快捷回复所属(0为公共,个人库为其id)',
  `Q_Createtime` int(11) NOT NULL COMMENT '创建时间',
  `Q_Updatetime` int(11) NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `quickreplylibrary`
--

INSERT INTO `quickreplylibrary` (`Q_ID`, `Q_classification`, `Q_Belongs`, `Q_Createtime`, `Q_Updatetime`) VALUES
(1, '常用语啊啊啊啊的啊啊啊啊啊啊啊啊', 0, 1, 1),
(6, '啊啊啊啊啊啊', 0, 1546688302, 1546688302),
(7, '啊啊啊啊啊啊啊啊啊', 0, 1546688357, 1546688357),
(8, '1111111111', 0, 1546688397, 1546688397),
(9, '222', 0, 1546688712, 1546688712),
(10, '48', 0, 1546688749, 1546688749),
(13, '不常用啊啊啊', 1, 11, 1),
(15, '啊啊啊', 1, 1546705341, 1546705341),
(16, '大大撒啊', 1, 1546705360, 1546705360);

-- --------------------------------------------------------

--
-- 表的结构 `robots`
--

CREATE TABLE `robots` (
  `R_ID` int(11) NOT NULL COMMENT '自增ID',
  `R_ImageSrc` varchar(255) NOT NULL COMMENT '机器人头像',
  `R_Name` varchar(16) NOT NULL COMMENT '机器人名称',
  `R_Note` varchar(16) NOT NULL COMMENT '机器人备注',
  `R_Welcome` varchar(255) NOT NULL COMMENT '机器人欢迎语',
  `R_Unknown` varchar(255) NOT NULL COMMENT '未知问题答案'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `robots`
--

INSERT INTO `robots` (`R_ID`, `R_ImageSrc`, `R_Name`, `R_Note`, `R_Welcome`, `R_Unknown`) VALUES
(1, 'centos2.huangdf.com/thinkcmf/public/upload/defaultPic/435b42670c23c984317ffe4cc3fed08f.jpg', '智客脑系客服小蓝啊啊啊回复', '一个超级可爱的客服，我知道鸭', '您好，我是智能客服小蓝哦！', '哎呀，您问清楚一点啦！！！');

-- --------------------------------------------------------

--
-- 表的结构 `rolesname`
--

CREATE TABLE `rolesname` (
  `R_ID` int(11) NOT NULL COMMENT '自增ID',
  `R_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '角色名称',
  `R_Type` int(11) NOT NULL COMMENT '角色类型/角色权限',
  `R_Description` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '角色描述'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `rolesname`
--

INSERT INTO `rolesname` (`R_ID`, `R_Name`, `R_Type`, `R_Description`) VALUES
(1, '超级管理员', 1, '包含所有客服和管理的权限'),
(2, '管理员', 2, '包含除呼叫功能以外所有客服和管理权限'),
(3, '普通客服', 3, '负责一线在线咨询的接待, 留言的处理'),
(13, '权限管理员', 2, '管理权限'),
(14, '客服一', 3, '用于回答问题'),
(15, '客服一组长', 0, '负责客服一组的签到');

-- --------------------------------------------------------

--
-- 表的结构 `rolestypes`
--

CREATE TABLE `rolestypes` (
  `RT_ID` int(11) NOT NULL COMMENT '自增主键',
  `RT_Name` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '角色类型/角色权限'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `rolestypes`
--

INSERT INTO `rolestypes` (`RT_ID`, `RT_Name`) VALUES
(1, '超级管理员'),
(2, '管理员'),
(3, '客服');

-- --------------------------------------------------------

--
-- 表的结构 `session`
--

CREATE TABLE `session` (
  `S_ID` int(11) NOT NULL COMMENT '自增主键',
  `S_Message` varchar(255) CHARACTER SET utf8 NOT NULL COMMENT '消息',
  `S_SendStatus` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '发送状态',
  `S_ReceiveStatus` varchar(16) CHARACTER SET utf8 NOT NULL COMMENT '接受状态',
  `S_SendID` varchar(255) NOT NULL COMMENT '发送者ID',
  `S_SendName` varchar(16) NOT NULL COMMENT '发送者姓名',
  `S_ReceiveID` varchar(255) NOT NULL COMMENT '接收者ID',
  `S_ReceiveName` varchar(16) NOT NULL COMMENT '接收者姓名',
  `S_SendTime` int(11) NOT NULL COMMENT '发送时间',
  `S_ReceiveTime` int(11) NOT NULL COMMENT '接收时间',
  `S_MessageType` int(4) NOT NULL COMMENT '消息类型'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `session`
--

INSERT INTO `session` (`S_ID`, `S_Message`, `S_SendStatus`, `S_ReceiveStatus`, `S_SendID`, `S_SendName`, `S_ReceiveID`, `S_ReceiveName`, `S_SendTime`, `S_ReceiveTime`, `S_MessageType`) VALUES
(1, 'dddd', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '2', 1547308012, 1547308012, 1),
(2, 'http://centos2.huangdf.com/thinkcmf/public/upload/chatPictures/20190112/d1116f73d2bf7c9879ed1cf027c2465f.jpg', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '2', 1547308444, 1547308444, 2),
(3, 'A', '已发送', '已接收', 'user.2', '2', 'server.1', '陈祥佳', 1547308608, 1547308608, 1),
(4, 'KK', '已发送', '已接收', 'user.2', '2', 'server.1', '陈祥佳', 1547308707, 1547308707, 1),
(5, '啊啊啊', '已发送', '已接收', 'user.2', '2', 'server.1', '陈祥佳', 1547308926, 1547308926, 1),
(6, '啊', '已发送', '已接收', 'user.2', '2', 'server.1', '陈祥佳', 1547309047, 1547309047, 1),
(7, 'a', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '2', 1547309057, 1547309057, 1),
(8, '在干嘛鸭', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.1', '陈祥佳', 1547367425, 1547367425, 1),
(9, '你踩踩', '已发送', '已接收', 'server.1', '陈祥佳', 'user.1', '哈哈哈哈哈', 1547367436, 1547367436, 1),
(10, '怎样申请课程', '已发送', '已接收', 'user.3', '3', 'server.49', '041', 1547370620, 1547370620, 1),
(11, '1', '已发送', '已接收', 'server.49', '041', 'user.3', '3', 1547370656, 1547370656, 1),
(12, '你', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547426915, 1547426915, 1),
(13, '来了老弟', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547426940, 1547426940, 1),
(14, 'http://centos2.huangdf.com/thinkcmf/public/upload/chatPictures/20190114/11c2aed1ea3c2dc93acb30d3ce0fed1f.JPG', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427027, 1547427027, 2),
(15, '课程小组有多少种情况', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427070, 1547427070, 1),
(16, '什么', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427081, 1547427081, 1),
(17, '什么', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427103, 1547427103, 1),
(18, 'fdst', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427115, 1547427115, 1),
(19, '塞哟娜拉', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427148, 1547427148, 1),
(20, '你好', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427410, 1547427410, 1),
(21, '什么', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427505, 1547427505, 1),
(22, '新年好', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427682, 1547427682, 1),
(23, '我没有啊', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427728, 1547427728, 1),
(24, '新年好', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427729, 1547427729, 1),
(25, '11', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427736, 1547427736, 1),
(26, '干嘛', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427745, 1547427745, 1),
(27, '46他', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427750, 1547427750, 1),
(28, '烦得很', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427763, 1547427763, 1),
(29, 'f', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427769, 1547427769, 1),
(30, '1', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427776, 1547427776, 1),
(31, 'fd', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427785, 1547427785, 1),
(32, '1', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427788, 1547427788, 1),
(33, 'aa', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427803, 1547427803, 1),
(34, 'm', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427807, 1547427807, 1),
(35, 'm', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427809, 1547427809, 1),
(36, 'c', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427820, 1547427820, 1),
(37, 'f', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427826, 1547427826, 1),
(38, 'd', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427831, 1547427831, 1),
(39, 'g', '已发送', '已接收', 'server.52', '曹日林', 'user.1', '哈哈哈哈哈', 1547427841, 1547427841, 1),
(40, '1', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427852, 1547427852, 1),
(41, '怎么老弟', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427892, 1547427892, 1),
(42, '1', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427898, 1547427898, 1),
(43, '1', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427910, 1547427910, 1),
(44, '2', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.52', '曹日林', 1547427920, 1547427920, 1),
(45, 'll', '已发送', '已接收', 'user.3', '3', 'server.52', '曹日林', 1547427970, 1547427970, 1),
(46, '来了老弟', '已发送', '已接收', 'user.3', '3', 'server.52', '曹日林', 1547427976, 1547427976, 1),
(47, '可以的', '已发送', '已接收', 'user.3', '3', 'server.52', '曹日林', 1547427985, 1547427985, 1),
(48, '3', '已发送', '已接收', 'user.3', '3', 'server.52', '曹日林', 1547428006, 1547428006, 1),
(49, '嗨', '已发送', '已接收', 'user.3', '3', 'server.49', '041', 1547431814, 1547431814, 1),
(50, '啊啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.1', '哈哈哈哈哈', 1547454323, 1547454323, 1),
(51, 'hi、', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547482045, 1547482045, 1),
(52, 'xxx', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547482122, 1547482122, 1),
(53, '1', '已发送', '已接收', 'user.3', '3', 'server.49', '041', 1547530296, 1547530296, 1),
(54, 'http://centos2.huangdf.com/thinkcmf/public/upload/chatPictures/20190115/7f331279094bbbbda648357766189764.jpg', '已发送', '已接收', 'user.3', '3', 'server.49', '041', 1547530398, 1547530398, 2),
(55, '#色;', '已发送', '已接收', 'user.3', '3', 'server.49', '041', 1547530466, 1547530466, 1),
(56, '？？', '已发送', '已接收', 'user.1', '哈哈哈哈哈', 'server.49', '041', 1547540941, 1547540941, 1),
(57, '飒', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547543543, 1547543543, 1),
(58, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547547677, 1547547677, 1),
(59, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547547918, 1547547918, 1),
(60, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547547999, 1547547999, 1),
(61, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547548002, 1547548002, 1),
(62, '是', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547548005, 1547548005, 1),
(63, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547556011, 1547556011, 1),
(64, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547556211, 1547556211, 1),
(65, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547556385, 1547556385, 1),
(66, '啊啊啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547556679, 1547556679, 1),
(67, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547556851, 1547556851, 1),
(68, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547557751, 1547557751, 1),
(69, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547557994, 1547557994, 1),
(70, '1', '已发送', '已接收', 'user.', '', 'server.1', '陈祥佳', 1547558178, 1547558178, 1),
(71, '课程小组有多少种情况', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547558226, 1547558226, 1),
(72, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547558878, 1547558878, 1),
(73, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547559213, 1547559213, 1),
(74, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560257, 1547560257, 1),
(75, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560527, 1547560527, 1),
(76, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560575, 1547560575, 1),
(77, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560628, 1547560628, 1),
(78, '事实上', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560630, 1547560630, 1),
(79, '是', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560653, 1547560653, 1),
(80, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560700, 1547560700, 1),
(81, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560704, 1547560704, 1),
(82, 's', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547560710, 1547560710, 1),
(83, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565254, 1547565254, 1),
(84, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565379, 1547565379, 1),
(85, 'd', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565452, 1547565452, 1),
(86, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565609, 1547565609, 1),
(87, '是', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565619, 1547565619, 1),
(88, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565744, 1547565744, 1),
(89, '大大大', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547565832, 1547565832, 1),
(90, '傻吊', '已发送', '已接收', 'user.3', '3', 'server.54', '1111', 1547568426, 1547568426, 1),
(91, '傻吊', '已发送', '已接收', 'user.3', '3', 'server.54', '1111', 1547568430, 1547568430, 1),
(92, '玩蛇呢', '已发送', '已接收', 'user.3', '3', 'server.54', '1111', 1547568436, 1547568436, 1),
(93, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547571389, 1547571389, 1),
(94, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547571655, 1547571655, 1),
(95, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547571712, 1547571712, 1),
(96, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547571862, 1547571862, 1),
(97, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547572025, 1547572025, 1),
(98, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547572517, 1547572517, 1),
(99, '#强;', '已发送', '已接收', 'user.2', '123', 'server.1', '陈祥佳', 1547572537, 1547572537, 1),
(100, 'a', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547574468, 1547574468, 1),
(101, '啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547575659, 1547575659, 1),
(102, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547575881, 1547575881, 1),
(103, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547575885, 1547575885, 1),
(104, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '123', 1547576106, 1547576106, 1),
(105, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547576167, 1547576167, 1),
(106, '啊啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547576478, 1547576478, 1),
(107, '的', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '123', 1547576482, 1547576482, 1),
(108, '的', '已发送', '已接收', 'server.1', '陈祥佳', 'user.2', '123', 1547576521, 1547576521, 1),
(109, '给', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547577078, 1547577078, 1),
(110, '啊', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547577416, 1547577416, 1),
(111, '的', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547577436, 1547577436, 1),
(112, '哈哈', '已发送', '已接收', 'user.2', '123', 'server.54', '1111', 1547601448, 1547601448, 1),
(113, '嗯嗯', '已发送', '已接收', 'server.54', '1111', 'user.2', '123', 1547601457, 1547601457, 1),
(114, 'http://centos2.huangdf.com/thinkcmf/public/upload/chatPictures/20190116/8ab57e76dfd1d20e699bc171bac05791.jpg', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547653279, 1547653279, 2),
(115, '啊啊啊啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654085, 1547654085, 1),
(116, '大大撒', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654089, 1547654089, 1),
(117, '啊啊啊', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654093, 1547654093, 1),
(118, '大苏打实打实d', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654098, 1547654098, 1),
(119, '你还', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654100, 1547654100, 1),
(120, '你好', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547654103, 1547654103, 1),
(121, '嘎嘎嘎', '已发送', '已接收', 'server.1', '陈祥佳', 'user.3', '3', 1547776588, 1547776588, 1),
(122, '哈哈哈', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547776597, 1547776597, 1),
(123, '看看就', '已发送', '已接收', 'user.3', '3', 'server.1', '陈祥佳', 1547776613, 1547776613, 1);

-- --------------------------------------------------------

--
-- 表的结构 `state`
--

CREATE TABLE `state` (
  `S_ID` int(11) NOT NULL COMMENT '自增主键',
  `S_Name` varchar(16) NOT NULL COMMENT '状态名称（在线、离开、离线、忙碌）'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `state`
--

INSERT INTO `state` (`S_ID`, `S_Name`) VALUES
(1, '在线'),
(2, '离开'),
(3, '离线'),
(4, '忙绿');

-- --------------------------------------------------------

--
-- 表的结构 `statistics`
--

CREATE TABLE `statistics` (
  `S_ID` int(10) NOT NULL,
  `S_Name` varchar(255) NOT NULL,
  `S_Num` int(10) NOT NULL,
  `S_Time` int(10) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=utf8;

-- --------------------------------------------------------

--
-- 表的结构 `unknown`
--

CREATE TABLE `unknown` (
  `Un_ID` int(11) NOT NULL COMMENT '自增id',
  `Un_Question` varchar(255) NOT NULL COMMENT '未知问题',
  `Un_Answer` varchar(255) NOT NULL COMMENT '未知问题答案',
  `Un_Createtime` int(11) NOT NULL COMMENT '未知问题创建时间'
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `unknown`
--

INSERT INTO `unknown` (`Un_ID`, `Un_Question`, `Un_Answer`, `Un_Createtime`) VALUES
(9, '问问', '撒旦撒a', 2);

-- --------------------------------------------------------

--
-- 替换视图以便查看 `userhome`
-- (See below for the actual view)
--
CREATE TABLE `userhome` (
`U_Address` varchar(100)
,`num` bigint(21)
);

-- --------------------------------------------------------

--
-- 表的结构 `users`
--

CREATE TABLE `users` (
  `U_ID` int(11) NOT NULL COMMENT '自增主键',
  `U_ImageSrc` varchar(255) NOT NULL COMMENT '头像路径',
  `U_Name` varchar(16) NOT NULL COMMENT '用户名',
  `U_Password` char(100) NOT NULL COMMENT '用户密码',
  `U_Sex` tinyint(2) NOT NULL COMMENT '性别;0:保密,1:男,2:女',
  `U_Address` varchar(100) NOT NULL COMMENT '地址',
  `U_Signature` varchar(16) NOT NULL COMMENT '个性签名',
  `U_StateID` int(11) NOT NULL COMMENT '状态（在线、离开、离线）',
  `U_Evaluation` varchar(16) DEFAULT NULL COMMENT '客服/管理员给用户的评价',
  `U_LastTime` int(11) NOT NULL COMMENT '最后登录时间',
  `U_LastIP` varchar(15) NOT NULL COMMENT '最后登录ip',
  `U_Createtime` int(11) NOT NULL COMMENT '创建时间',
  `U_Updatetime` int(11) NOT NULL COMMENT '更新时间'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 ROW_FORMAT=DYNAMIC;

--
-- 转存表中的数据 `users`
--

INSERT INTO `users` (`U_ID`, `U_ImageSrc`, `U_Name`, `U_Password`, `U_Sex`, `U_Address`, `U_Signature`, `U_StateID`, `U_Evaluation`, `U_LastTime`, `U_LastIP`, `U_Createtime`, `U_Updatetime`) VALUES
(1, 'centos2.huangdf.com/thinkcmf/public/upload/userHead/20190114/5930957924f8902a0885c62b20d5efea.JPG', '哈哈哈哈哈', '1', 0, '广东广州', '我爱你傻吊', 1, '19.9', 1547539641, '172.17.146.121', 111, 1),
(2, 'centos2.huangdf.com/thinkcmf/public/upload/userHead/20190116/64f7b78597eacba1d9624c7fb33d6fb4.jpg', '123', '2', 0, '2', '2', 1, '2', 1547627503, '172.18.215.20', 2, 2),
(3, 'centos2.huangdf.com/thinkcmf/public/upload/userHead/20190112/383c3dc43d6c0b5499974a206d85ea31.jpg', '3', '3', 1, '广东佛山', '新年快乐', 1, '11.9', 1547776966, '172.17.4.241', 3, 3);

-- --------------------------------------------------------

--
-- 表的结构 `usertooken`
--

CREATE TABLE `usertooken` (
  `US_ID` int(11) NOT NULL,
  `US_User_ID` int(11) NOT NULL COMMENT '用户id',
  `US_User_Expire_Time` int(11) NOT NULL COMMENT '过期时间',
  `US_User_Create_Time` int(11) NOT NULL COMMENT '创建时间',
  `US_User_Token` varchar(64) CHARACTER SET utf8 NOT NULL COMMENT 'token'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- 转存表中的数据 `usertooken`
--

INSERT INTO `usertooken` (`US_ID`, `US_User_ID`, `US_User_Expire_Time`, `US_User_Create_Time`, `US_User_Token`) VALUES
(1, 1, 1563091641, 1547539641, '480e0ca88c14596ad43039674ba0fd6cb7d96101cb642d59f9106d505f06d2ff'),
(2, 2, 1563179503, 1547627503, 'f078aacbaafd467823eea80b7ae54cc420fa3a50c470463ddad3b5be29c655c1'),
(3, 3, 1563328966, 1547776966, 'f9c12c9ec816f788ee4058c9242dacb826ecca1b72c8a8e6760c3e6bd7af669b'),
(4, 4, 1561439539, 1545887539, 'ee811313e7172b879ab932a75503f9157a6ad4703ba2126f0b8a77761e34ee9f');

-- --------------------------------------------------------

--
-- 视图结构 `adminhome`
--
DROP TABLE IF EXISTS `adminhome`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `adminhome`  AS  select `admins`.`A_Address` AS `A_Address`,count(0) AS `num` from `admins` group by `admins`.`A_Address` ;

-- --------------------------------------------------------

--
-- 视图结构 `allDate`
--
DROP TABLE IF EXISTS `allDate`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `allDate`  AS  select `admins`.`A_ID` AS `A_ID`,`admins`.`A_Name` AS `A_Name`,`admins`.`A_Email` AS `A_Email`,`admins`.`A_Maximum` AS `A_Maximum`,`admins`.`A_Credibility` AS `A_Credibility`,`admins`.`A_Experience` AS `A_Experience`,`admins`.`A_Attitude` AS `A_Attitude`,`groups`.`G_Name` AS `G_Name`,`state`.`S_Name` AS `S_Name`,`accountstatus`.`AS_Name` AS `AS_Name`,`rolestypes`.`RT_Name` AS `RT_Name` from ((((`admins` join `groups`) join `state`) join `accountstatus`) join `rolestypes`) where ((`admins`.`A_Group` = `groups`.`G_ID`) and (`admins`.`A_StateID` = `state`.`S_ID`) and (`admins`.`A_CheckStatues` = `accountstatus`.`AS_ID`) and (`admins`.`A_Level` = `rolestypes`.`RT_ID`)) group by `admins`.`A_ID` ;

-- --------------------------------------------------------

--
-- 视图结构 `allgroup`
--
DROP TABLE IF EXISTS `allgroup`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `allgroup`  AS  select `admins`.`A_ID` AS `A_ID`,`admins`.`A_Name` AS `A_Name`,`groups`.`G_Name` AS `G_Name`,`groups`.`G_ID` AS `G_ID` from (`groups` left join `admins` on((`admins`.`A_Group` = `groups`.`G_ID`))) ;

-- --------------------------------------------------------

--
-- 视图结构 `allRole`
--
DROP TABLE IF EXISTS `allRole`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `allRole`  AS  select `rolesname`.`R_ID` AS `R_ID`,`rolesname`.`R_Name` AS `R_Name`,`rolestypes`.`RT_Name` AS `RT_Name`,`rolesname`.`R_Description` AS `R_Description` from (`rolesname` join `rolestypes`) where (`rolesname`.`R_Type` = `rolestypes`.`RT_ID`) group by `rolesname`.`R_ID` ;

-- --------------------------------------------------------

--
-- 视图结构 `cardsbody`
--
DROP TABLE IF EXISTS `cardsbody`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `cardsbody`  AS  select `allgroup`.`G_ID` AS `G_ID`,`allgroup`.`G_Name` AS `G_Name`,group_concat(`allgroup`.`A_Name` separator ';\n') AS `A_Name` from `allgroup` group by `allgroup`.`G_Name`,`allgroup`.`G_ID` ;

-- --------------------------------------------------------

--
-- 视图结构 `userhome`
--
DROP TABLE IF EXISTS `userhome`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `userhome`  AS  select `users`.`U_Address` AS `U_Address`,count(0) AS `num` from `users` group by `users`.`U_Address` ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `accountstatus`
--
ALTER TABLE `accountstatus`
  ADD PRIMARY KEY (`AS_ID`);

--
-- Indexes for table `adminleavewords`
--
ALTER TABLE `adminleavewords`
  ADD PRIMARY KEY (`L_ID`);

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`A_ID`) USING BTREE;

--
-- Indexes for table `admintooken`
--
ALTER TABLE `admintooken`
  ADD PRIMARY KEY (`AT_ID`);

--
-- Indexes for table `classify`
--
ALTER TABLE `classify`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `datastatistics`
--
ALTER TABLE `datastatistics`
  ADD PRIMARY KEY (`D_ID`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`F_ID`) USING BTREE,
  ADD KEY `fk_mtypes` (`F_MessagesTypes`) USING BTREE,
  ADD KEY `fk_aID` (`F_UserID`) USING BTREE;

--
-- Indexes for table `firm`
--
ALTER TABLE `firm`
  ADD PRIMARY KEY (`F_ID`);

--
-- Indexes for table `groups`
--
ALTER TABLE `groups`
  ADD PRIMARY KEY (`G_ID`);

--
-- Indexes for table `history`
--
ALTER TABLE `history`
  ADD PRIMARY KEY (`H_ID`);

--
-- Indexes for table `leavewords`
--
ALTER TABLE `leavewords`
  ADD PRIMARY KEY (`L_ID`);

--
-- Indexes for table `messagestype`
--
ALTER TABLE `messagestype`
  ADD PRIMARY KEY (`M_Id`) USING BTREE;

--
-- Indexes for table `personallibrary`
--
ALTER TABLE `personallibrary`
  ADD PRIMARY KEY (`P_ID`);

--
-- Indexes for table `pointknowledge`
--
ALTER TABLE `pointknowledge`
  ADD PRIMARY KEY (`PK_ID`);

--
-- Indexes for table `publiclibrary`
--
ALTER TABLE `publiclibrary`
  ADD PRIMARY KEY (`P_ID`);

--
-- Indexes for table `question`
--
ALTER TABLE `question`
  ADD PRIMARY KEY (`Q_ID`);

--
-- Indexes for table `quickreplylibrary`
--
ALTER TABLE `quickreplylibrary`
  ADD PRIMARY KEY (`Q_ID`);

--
-- Indexes for table `robots`
--
ALTER TABLE `robots`
  ADD PRIMARY KEY (`R_ID`);

--
-- Indexes for table `rolesname`
--
ALTER TABLE `rolesname`
  ADD PRIMARY KEY (`R_ID`);

--
-- Indexes for table `rolestypes`
--
ALTER TABLE `rolestypes`
  ADD PRIMARY KEY (`RT_ID`);

--
-- Indexes for table `session`
--
ALTER TABLE `session`
  ADD PRIMARY KEY (`S_ID`);

--
-- Indexes for table `state`
--
ALTER TABLE `state`
  ADD PRIMARY KEY (`S_ID`) USING BTREE;

--
-- Indexes for table `statistics`
--
ALTER TABLE `statistics`
  ADD PRIMARY KEY (`S_ID`);

--
-- Indexes for table `unknown`
--
ALTER TABLE `unknown`
  ADD PRIMARY KEY (`Un_ID`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`U_ID`) USING BTREE,
  ADD KEY `fk_ustate` (`U_StateID`) USING BTREE;

--
-- Indexes for table `usertooken`
--
ALTER TABLE `usertooken`
  ADD PRIMARY KEY (`US_ID`);

--
-- 在导出的表使用AUTO_INCREMENT
--

--
-- 使用表AUTO_INCREMENT `accountstatus`
--
ALTER TABLE `accountstatus`
  MODIFY `AS_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=3;
--
-- 使用表AUTO_INCREMENT `adminleavewords`
--
ALTER TABLE `adminleavewords`
  MODIFY `L_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id', AUTO_INCREMENT=11;
--
-- 使用表AUTO_INCREMENT `admins`
--
ALTER TABLE `admins`
  MODIFY `A_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=56;
--
-- 使用表AUTO_INCREMENT `admintooken`
--
ALTER TABLE `admintooken`
  MODIFY `AT_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=13;
--
-- 使用表AUTO_INCREMENT `classify`
--
ALTER TABLE `classify`
  MODIFY `id` int(19) NOT NULL AUTO_INCREMENT COMMENT '知识分类id,唯一', AUTO_INCREMENT=130;
--
-- 使用表AUTO_INCREMENT `datastatistics`
--
ALTER TABLE `datastatistics`
  MODIFY `D_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=2;
--
-- 使用表AUTO_INCREMENT `firm`
--
ALTER TABLE `firm`
  MODIFY `F_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=17;
--
-- 使用表AUTO_INCREMENT `groups`
--
ALTER TABLE `groups`
  MODIFY `G_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=21;
--
-- 使用表AUTO_INCREMENT `history`
--
ALTER TABLE `history`
  MODIFY `H_ID` int(10) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=50;
--
-- 使用表AUTO_INCREMENT `leavewords`
--
ALTER TABLE `leavewords`
  MODIFY `L_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id', AUTO_INCREMENT=16;
--
-- 使用表AUTO_INCREMENT `personallibrary`
--
ALTER TABLE `personallibrary`
  MODIFY `P_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=5;
--
-- 使用表AUTO_INCREMENT `pointknowledge`
--
ALTER TABLE `pointknowledge`
  MODIFY `PK_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '知识点自增ID', AUTO_INCREMENT=9;
--
-- 使用表AUTO_INCREMENT `publiclibrary`
--
ALTER TABLE `publiclibrary`
  MODIFY `P_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=22;
--
-- 使用表AUTO_INCREMENT `question`
--
ALTER TABLE `question`
  MODIFY `Q_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '问题的自增ID', AUTO_INCREMENT=1571;
--
-- 使用表AUTO_INCREMENT `quickreplylibrary`
--
ALTER TABLE `quickreplylibrary`
  MODIFY `Q_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=17;
--
-- 使用表AUTO_INCREMENT `rolesname`
--
ALTER TABLE `rolesname`
  MODIFY `R_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增ID', AUTO_INCREMENT=17;
--
-- 使用表AUTO_INCREMENT `rolestypes`
--
ALTER TABLE `rolestypes`
  MODIFY `RT_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=4;
--
-- 使用表AUTO_INCREMENT `session`
--
ALTER TABLE `session`
  MODIFY `S_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=124;
--
-- 使用表AUTO_INCREMENT `statistics`
--
ALTER TABLE `statistics`
  MODIFY `S_ID` int(10) NOT NULL AUTO_INCREMENT;
--
-- 使用表AUTO_INCREMENT `unknown`
--
ALTER TABLE `unknown`
  MODIFY `Un_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增id', AUTO_INCREMENT=10;
--
-- 使用表AUTO_INCREMENT `users`
--
ALTER TABLE `users`
  MODIFY `U_ID` int(11) NOT NULL AUTO_INCREMENT COMMENT '自增主键', AUTO_INCREMENT=4;
--
-- 使用表AUTO_INCREMENT `usertooken`
--
ALTER TABLE `usertooken`
  MODIFY `US_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;
--
-- 限制导出的表
--

--
-- 限制表 `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `fk_mtypes` FOREIGN KEY (`F_MessagesTypes`) REFERENCES `messagestype` (`M_Id`),
  ADD CONSTRAINT `fk_uID` FOREIGN KEY (`F_UserID`) REFERENCES `users` (`U_ID`);

DELIMITER $$
--
-- 事件
--
CREATE DEFINER=`root`@`localhost` EVENT `cleanEventJob` ON SCHEDULE EVERY 1 DAY STARTS '2019-01-04 00:00:00' ON COMPLETION PRESERVE ENABLE DO call clean()$$

CREATE DEFINER=`root`@`localhost` EVENT `insertDateEventJob` ON SCHEDULE EVERY 1 HOUR STARTS '2019-01-04 00:00:00' ON COMPLETION PRESERVE ENABLE DO call insertDate()$$

DELIMITER ;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
