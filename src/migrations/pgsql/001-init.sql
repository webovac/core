CREATE SEQUENCE "public"."file_id_seq";
CREATE TABLE "public"."file" (
    "id" int4 NOT NULL DEFAULT nextval('file_id_seq'::regclass),
    "identifier" varchar NOT NULL,
    "modern_identifier" varchar,
    "compatible_identifier" varchar,
    "name" varchar NOT NULL,
    "extension" varchar NOT NULL,
    "content_type" varchar NOT NULL,
    "type" varchar NOT NULL,
    "created_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    UNIQUE ("identifier"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."file" ("created_by_person_id");

CREATE SEQUENCE "public"."language_id_seq";
CREATE TABLE "public"."language" (
    "id" int4 NOT NULL DEFAULT nextval('language_id_seq'::regclass),
    "shortcut" varchar NOT NULL,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("shortcut"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."language" ("created_by_person_id");
CREATE INDEX ON "public"."language" ("updated_by_person_id");

CREATE SEQUENCE "public"."language_translation_id_seq";
CREATE TABLE "public"."language_translation" (
    "id" int4 NOT NULL DEFAULT nextval('language_translation_id_seq'::regclass),
    "language_id" int4 NOT NULL,
    "translation_language_id" int4 NOT NULL,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "title" varchar NOT NULL,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("language_id", "translation_language_id"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."language_translation" ("created_by_person_id");
CREATE INDEX ON "public"."language_translation" ("language_id");
CREATE INDEX ON "public"."language_translation" ("translation_language_id");
CREATE INDEX ON "public"."language_translation" ("updated_by_person_id");

CREATE SEQUENCE "public"."module_id_seq";
CREATE TABLE "public"."module" (
    "id" int4 NOT NULL DEFAULT nextval('module_id_seq'::regclass),
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "name" varchar NOT NULL,
    "icon" varchar,
    "created_at" timestamp DEFAULT now(),
    "updated_at" timestamp,
    "home_page_id" int4,
    UNIQUE ("name"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."module" ("created_by_person_id");
CREATE INDEX ON "public"."module" ("updated_by_person_id");

CREATE TABLE "public"."module2web" (
    "module_id" int4 NOT NULL,
    "web_id" int4 NOT NULL,
    PRIMARY KEY ("module_id", "web_id")
);
CREATE INDEX ON "public"."module2web" ("module_id");
CREATE INDEX ON "public"."module2web" ("web_id");

CREATE SEQUENCE "public"."module_translation_id_seq";
CREATE TABLE "public"."module_translation" (
    "id" int4 NOT NULL DEFAULT nextval('module_translation_id_seq'::regclass),
    "module_id" int4,
    "language_id" int4,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "title" varchar NOT NULL,
    "description" text,
    "base_path" varchar,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("module_id", "language_id"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."module_translation" ("created_by_person_id");
CREATE INDEX ON "public"."module_translation" ("language_id");
CREATE INDEX ON "public"."module_translation" ("module_id");
CREATE INDEX ON "public"."module_translation" ("updated_by_person_id");

CREATE SEQUENCE "public"."page_id_seq";
CREATE TABLE "public"."page" (
    "id" int4 NOT NULL DEFAULT nextval('page_id_seq'::regclass),
    "web_id" int4,
    "parent_page_id" int4,
    "has_parameter" bool NOT NULL DEFAULT false,
    "icon" varchar,
    "redirect_page_id" int4,
    "module_id" int4,
    "template_page_id" int4,
    "image_file_id" int4,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    "published_at" timestamp,
    "name" varchar,
    "style" varchar,
    "repository" varchar,
    "provides_navigation" bool NOT NULL DEFAULT false,
    "rank" int4 NOT NULL DEFAULT 1,
    "hide_in_navigation" bool NOT NULL DEFAULT false,
    "has_parent_parameter" bool NOT NULL DEFAULT false,
    "parent_repository" varchar,
    "provides_buttons" bool NOT NULL DEFAULT false,
    "dont_inherit_path" bool NOT NULL DEFAULT false,
    "dont_inherit_access_setup" bool NOT NULL DEFAULT false,
    "target_page_id" int4,
    "target_parameter" varchar,
    "target_parent_parameter" varchar,
    "target_url" varchar,
    "type" varchar NOT NULL DEFAULT 'page',
    "access_for" varchar NOT NULL DEFAULT 'all',
    "authorizing_tag" varchar,
    "authorizing_parent_tag" varchar,
    "target_signal" varchar,
    "stretched" bool NOT NULL DEFAULT false,
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."page" ("created_by_person_id");
CREATE INDEX ON "public"."page" ("has_parameter");
CREATE INDEX ON "public"."page" ("module_id");
CREATE INDEX ON "public"."page" ("parent_page_id");
CREATE INDEX ON "public"."page" ("redirect_page_id");
CREATE INDEX ON "public"."page" ("template_page_id");
CREATE INDEX ON "public"."page" ("image_file_id");
CREATE INDEX ON "public"."page" ("web_id");
CREATE INDEX ON "public"."page" ("type");
CREATE INDEX ON "public"."page" ("updated_by_person_id");

CREATE TABLE "public"."page2authorized_person" (
    "page_id" int4 NOT NULL,
    "person_id" int4 NOT NULL,
    PRIMARY KEY ("page_id", "person_id")
);

CREATE TABLE "public"."page2authorized_role" (
    "page_id" int4 NOT NULL,
    "role_id" int4 NOT NULL,
    PRIMARY KEY ("page_id", "role_id")
);

CREATE SEQUENCE "public"."page_translation_id_seq";
CREATE TABLE "public"."page_translation" (
    "id" int4 NOT NULL DEFAULT nextval('page_translation_id_seq'::regclass),
    "page_id" int4,
    "language_id" int4 NOT NULL,
    "path" varchar,
    "title" varchar,
    "description" text,
    "onclick" text,
    "content" text,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("page_id", "language_id"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."page_translation" ("created_by_person_id");
CREATE INDEX ON "public"."page_translation" ("language_id");
CREATE INDEX ON "public"."page_translation" ("page_id");
CREATE INDEX ON "public"."page_translation" ("updated_by_person_id");

CREATE SEQUENCE "public"."person_id_seq";
CREATE TABLE "public"."person" (
    "id" int4 NOT NULL DEFAULT nextval('person_id_seq'::regclass),
    "email" varchar,
    "first_name" varchar NOT NULL,
    "last_name" varchar NOT NULL,
    "last_login_at" timestamp,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("email"),
    PRIMARY KEY ("id")
);

CREATE SEQUENCE "public"."preference_id_seq";
CREATE TABLE "public"."preference" (
    "id" int4 NOT NULL DEFAULT nextval('preference_id_seq'::regclass),
    "person_id" int4 NOT NULL,
    "language_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    "web_id" int4,
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."preference" ("language_id");
CREATE INDEX ON "public"."preference" ("person_id");

CREATE SEQUENCE "public"."role_id_seq";
CREATE TABLE "public"."role" (
    "id" int4 NOT NULL DEFAULT nextval('role_id_seq'::regclass),
    "code" varchar NOT NULL,
    UNIQUE ("code"),
    PRIMARY KEY ("id")
);

CREATE TABLE "public"."role2person" (
    "role_id" int4 NOT NULL,
    "person_id" int4 NOT NULL,
    PRIMARY KEY ("role_id", "person_id")
);
CREATE INDEX ON "public"."role2person" ("role_id");
CREATE INDEX ON "public"."role2person" ("person_id");

CREATE SEQUENCE "public"."web_id_seq";
CREATE TABLE "public"."web" (
    "id" int4 NOT NULL DEFAULT nextval('web_id_seq'::regclass),
    "color" varchar,
    "complementary_color" varchar NOT NULL DEFAULT '#888888',
    "host" varchar NOT NULL,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    "published_at" timestamp,
    "base_path" varchar NOT NULL DEFAULT '',
    "icon_background_color" varchar NOT NULL DEFAULT '#ffffff',
    "home_page_id" int4,
    "default_language_id" int4,
    "icon_file_id" int4,
    "large_icon_file_id" int4,
    "logo_file_id" int4,
    "background_file_id" int4,
    "code" varchar(255) NOT NULL,
    UNIQUE ("host", "base_path"),
    UNIQUE ("code"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."web" ("base_path");
CREATE INDEX ON "public"."web" ("created_by_person_id");
CREATE INDEX ON "public"."web" ("default_language_id");
CREATE INDEX ON "public"."web" ("home_page_id");
CREATE INDEX ON "public"."web" ("icon_file_id");
CREATE INDEX ON "public"."web" ("large_icon_file_id");
CREATE INDEX ON "public"."web" ("logo_file_id");
CREATE INDEX ON "public"."web" ("background_file_id");
CREATE INDEX ON "public"."web" ("updated_by_person_id");

CREATE SEQUENCE "public"."web_translation_id_seq";
CREATE TABLE "public"."web_translation" (
    "id" int4 NOT NULL DEFAULT nextval('web_translation_id_seq'::regclass),
    "web_id" int4 NOT NULL,
    "language_id" int4 NOT NULL,
    "title" varchar NOT NULL,
    "footer" text,
    "created_by_person_id" int4,
    "updated_by_person_id" int4,
    "created_at" timestamp NOT NULL DEFAULT now(),
    "updated_at" timestamp,
    UNIQUE ("web_id", "language_id"),
    PRIMARY KEY ("id")
);
CREATE INDEX ON "public"."web_translation" ("created_by_person_id");
CREATE INDEX ON "public"."web_translation" ("language_id");
CREATE INDEX ON "public"."web_translation" ("updated_by_person_id");
CREATE INDEX ON "public"."web_translation" ("web_id");


ALTER TABLE "public"."file" ADD CONSTRAINT "file_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."language" ADD CONSTRAINT "language_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."language" ADD CONSTRAINT "language_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."language_translation" ADD CONSTRAINT "language_translation_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."language_translation" ADD CONSTRAINT "language_translation_language_id_fkey" FOREIGN KEY ("language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."language_translation" ADD CONSTRAINT "language_translation_translation_language_id_fkey" FOREIGN KEY ("translation_language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."language_translation" ADD CONSTRAINT "language_translation_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."module" ADD CONSTRAINT "module_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."module" ADD CONSTRAINT "module_home_page_id_fkey" FOREIGN KEY ("home_page_id") REFERENCES "public"."page" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."module" ADD CONSTRAINT "module_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."module2web" ADD CONSTRAINT "module2web_module_id_fkey" FOREIGN KEY ("module_id") REFERENCES "public"."module" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."module2web" ADD CONSTRAINT "module2web_web_id_fkey" FOREIGN KEY ("web_id") REFERENCES "public"."web" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."module_translation" ADD CONSTRAINT "module_translation_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."module_translation" ADD CONSTRAINT "module_translation_language_id_fkey" FOREIGN KEY ("language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."module_translation" ADD CONSTRAINT "module_translation_module_id_fkey" FOREIGN KEY ("module_id") REFERENCES "public"."module" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."module_translation" ADD CONSTRAINT "module_translation_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."page" ADD CONSTRAINT "page_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_module_id_fkey" FOREIGN KEY ("module_id") REFERENCES "public"."module" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_parent_page_id_fkey" FOREIGN KEY ("parent_page_id") REFERENCES "public"."page" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_redirect_page_id_fkey" FOREIGN KEY ("redirect_page_id") REFERENCES "public"."page" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_target_page_id_fkey" FOREIGN KEY ("target_page_id") REFERENCES "public"."page" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_template_page_id_fkey" FOREIGN KEY ("template_page_id") REFERENCES "public"."page" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_web_id_fkey" FOREIGN KEY ("web_id") REFERENCES "public"."web" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."page" ADD CONSTRAINT "page_file_id_fkey" FOREIGN KEY ("image_file_id") REFERENCES "public"."web" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."page2authorized_person" ADD CONSTRAINT "page2authorized_person_page_id_fkey" FOREIGN KEY ("page_id") REFERENCES "public"."page" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."page2authorized_person" ADD CONSTRAINT "page2authorized_person_person_id_fkey" FOREIGN KEY ("person_id") REFERENCES "public"."person" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."page2authorized_role" ADD CONSTRAINT "page2authorized_role_page_id_fkey" FOREIGN KEY ("page_id") REFERENCES "public"."page" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."page2authorized_role" ADD CONSTRAINT "page2authorized_role_role_id_fkey" FOREIGN KEY ("role_id") REFERENCES "public"."role" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."page_translation" ADD CONSTRAINT "page_translation_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page_translation" ADD CONSTRAINT "page_translation_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."page_translation" ADD CONSTRAINT "page_translation_language_id_fkey" FOREIGN KEY ("language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."page_translation" ADD CONSTRAINT "page_translation_page_id_fkey" FOREIGN KEY ("page_id") REFERENCES "public"."page" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."preference" ADD CONSTRAINT "preference_language_id_fkey" FOREIGN KEY ("language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."preference" ADD CONSTRAINT "preference_person_id_fkey" FOREIGN KEY ("person_id") REFERENCES "public"."person" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."preference" ADD CONSTRAINT "preference_web_id_fkey" FOREIGN KEY ("web_id") REFERENCES "public"."web" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."role2person" ADD CONSTRAINT "role2person_role_id_fkey" FOREIGN KEY ("role_id") REFERENCES "public"."role" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."role2person" ADD CONSTRAINT "role2person_person_id_fkey" FOREIGN KEY ("person_id") REFERENCES "public"."person" ("id") ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE "public"."web" ADD CONSTRAINT "web_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_default_language_id_fkey" FOREIGN KEY ("default_language_id") REFERENCES "public"."language" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_icon_id_fkey" FOREIGN KEY ("icon_file_id") REFERENCES "public"."file" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_large_icon_id_fkey" FOREIGN KEY ("large_icon_file_id") REFERENCES "public"."file" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_logo_file_id_fkey" FOREIGN KEY ("logo_file_id") REFERENCES "public"."file" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_background_file_id_fkey" FOREIGN KEY ("background_file_id") REFERENCES "public"."file" ("id") ON DELETE SET NULL ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_home_page_id_fkey" FOREIGN KEY ("home_page_id") REFERENCES "public"."page" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."web" ADD CONSTRAINT "web_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;

ALTER TABLE "public"."web_translation" ADD CONSTRAINT "web_translation_created_by_person_id_fkey" FOREIGN KEY ("created_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."web_translation" ADD CONSTRAINT "web_translation_updated_by_person_id_fkey" FOREIGN KEY ("updated_by_person_id") REFERENCES "public"."person" ("id") ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE "public"."web_translation" ADD CONSTRAINT "web_translation_language_id_fkey" FOREIGN KEY ("language_id") REFERENCES "public"."language" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
ALTER TABLE "public"."web_translation" ADD CONSTRAINT "web_translation_web_id_fkey" FOREIGN KEY ("web_id") REFERENCES "public"."web" ("id") ON DELETE CASCADE ON UPDATE CASCADE;
