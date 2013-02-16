============================
Cempori Dev 
============================

工作流程
===========

1. 唐建法为所需要工作建立Github issue（类别：Enhancement)， 并指定给小李
2. 小李收到邮件后对项目进行粗略评估，有不详细处和唐建法交流并记录到github issue 内
3. 小李给出所需时间的大致估计，记录在Github issue内
4. 唐建法留言同意进行
5. 小李备份当前服务器状态（数据库和Git Commit）
6. 小李开始工作，完成后写好文档，通知唐验收，并按照实际所需时间用淘宝或者支付宝开出账单
7. 唐测试软件试用，如有bug，开新的Github Issue给小李 （类别：Bug)，小李fix bug
8. 如系统没有bug，使用正常，唐支付账单 

注解：
  功能性的Bug不计时不收费

Hardware/Software Requirements  
============================
- CentOS 6.3
- LAMP 
- Server IP: 198.101.201.124
- User/password: magento/maxxxxxxx
- Install directory: /opt/cempori




Git and Checkout
============================
- Install Git client: http://git-scm.com/downloads
- Setup SSH keys accordingly: https://github.com/settings/ssh
- Open a shell window and run::


	  	git clone git@github.com:precon/cempori.git
	  	cd cempori 


 



