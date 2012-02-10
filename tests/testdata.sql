INSERT INTO "posts" VALUES(1,'2012-02-08T13:44:13+01:00','test','test','Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor incididunt ut labore et dolore magna aliqua. Ut enim ad minim veniam, quis nostrud exercitation ullamco laboris nisi ut aliquip ex ea commodo consequat. Duis aute irure dolor in reprehenderit in voluptate velit esse cillum dolore eu fugiat nulla pariatur. Excepteur sint occaecat cupidatat non proident, sunt in culpa qui officia deserunt mollit anim id est laborum.',NULL,'publish','','blog',0);

INSERT INTO "posts" VALUES(2,'2011-08-20T12:07:00+02:00','Ettelsberg 2','photo-ettelsberg-2','<a href="http://idisk.beerta.net/public/wordpress/Ettelsberg_252520August_25252020_25252C_2525202011.jpg" title="Ettelsberg 2"><img src="http://idisk.beerta.net/public/wordpress/thumb_Ettelsberg_252520August_25252020_25252C_2525202011.jpg"></a>','Ettelsberg_252520August_25252020_25252C_2525202011.jpg','publish','https://picasaweb.google.com/106832871642761506709/MyPhotography#5656660353731572658','photo',0);

INSERT INTO "post_terms" VALUES(1,'Test','test');
INSERT INTO "post_terms" VALUES(2,'Photography','photography');
INSERT INTO "post_terms" VALUES(3,'Article','article');

INSERT INTO "term_relations" VALUES(1,1);
INSERT INTO "term_relations" VALUES(2,2);
INSERT INTO "term_relations" VALUES(1,3);
