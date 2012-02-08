CREATE TABLE "comments" (
    ID INTEGER PRIMARY KEY NOT NULL, 
    post_ID INTEGER NOT NULL, 
    comment_author TEXT NOT NULL, 
    comment_author_email TEXT, 
    comment_author_url TEXT, 
    comment_date TEXT NOT NULL, 
    comment_content TEXT NOT NULL, 
    comment_status TEXT NOT NULL DEFAULT 'hidden', 
    original_source TEXT
);

CREATE TABLE "post_meta" (
    "post_meta_id" INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL,
    "posts_ID" INTEGER NOT NULL,
    "meta_key" TEXT NOT NULL,
    "meta_value" TEXT NOT NULL
);

CREATE TABLE "post_terms" (
    ID INTEGER PRIMARY KEY NOT NULL, 
    name TEXT NOT NULL, 
    slug TEXT NOT NULL
);

CREATE TABLE "posts" (
    "ID" INTEGER PRIMARY KEY,
    "post_date" TEXT NOT NULL,
    "post_title" TEXT NOT NULL,
    "post_slug" TEXT NOT NULL,
    "post_content" TEXT,
    "guid" TEXT,
    "post_status" TEXT NOT NULL DEFAULT ('publish'),
    "original_source" TEXT,
    "post_type" TEXT NOT NULL DEFAULT ('blog'), 
    "protected" INTEGER   DEFAULT (0)
);

CREATE TABLE "term_relations" (
    posts_ID INTEGER NOT NULL, 
    post_terms_ID INTEGER NOT NULL
);

CREATE UNIQUE INDEX relation ON term_relations (posts_ID ASC, post_terms_ID ASC);
CREATE UNIQUE INDEX slug ON post_terms (slug ASC);

