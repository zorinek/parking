-- Adminer 4.8.1 PostgreSQL 12.14 dump

DROP TABLE IF EXISTS "campaigns";
DROP SEQUENCE IF EXISTS campaigns_cam_id_seq;
CREATE SEQUENCE campaigns_cam_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."campaigns" (
    "cam_id" integer DEFAULT nextval('campaigns_cam_id_seq') NOT NULL,
    "pro_id" integer,
    "cam_name" character varying,
    CONSTRAINT "campaigns_pkey" PRIMARY KEY ("cam_id")
) WITH (oids = false);

CREATE INDEX "fki_cam_fkey_pro" ON "public"."campaigns" USING btree ("pro_id");


DROP TABLE IF EXISTS "campaigns_segments";
DROP SEQUENCE IF EXISTS campaigns_segments_cas_id_seq;
CREATE SEQUENCE campaigns_segments_cas_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."campaigns_segments" (
    "cas_id" integer DEFAULT nextval('campaigns_segments_cas_id_seq') NOT NULL,
    "cam_id" integer,
    "seg_id" integer,
    "cas_parkingdetected_left" integer,
    "cas_parkingfree_left" integer,
    "cas_parkingillegal_left" integer,
    "cas_parkingnotdetected_left" integer,
    "cas_parkingdetected_right" integer,
    "cas_parkingfree_right" integer,
    "cas_parkingillegal_right" integer,
    "cas_parkingnotdetected_right" integer,
    "cas_parkingdetected" integer,
    "cas_parkingfree" integer,
    "cas_parkingillegal" integer,
    "cas_parkingnotdetected" integer,
    "cas_done" integer DEFAULT '0' NOT NULL,
    "use_id" integer,
    "cas_datetime_reservation" timestamp,
    CONSTRAINT "campaigns_segments_pkey" PRIMARY KEY ("cas_id")
) WITH (oids = false);

CREATE INDEX "fki_cas_fkey_cam" ON "public"."campaigns_segments" USING btree ("cam_id");

CREATE INDEX "fki_cas_fkey_seg" ON "public"."campaigns_segments" USING btree ("seg_id");


DROP TABLE IF EXISTS "contactform";
DROP SEQUENCE IF EXISTS contactform_con_id_seq;
CREATE SEQUENCE contactform_con_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."contactform" (
    "con_id" integer DEFAULT nextval('contactform_con_id_seq') NOT NULL,
    "con_email" character varying(255) NOT NULL,
    "con_message" text NOT NULL,
    "con_name" character varying(255),
    "con_phone" character varying(63),
    "con_category" integer,
    "con_sender" integer,
    "con_datetimeinsert" timestamp NOT NULL,
    "use_id" integer,
    "con_datetimedone" timestamp,
    CONSTRAINT "contactform_pkey" PRIMARY KEY ("con_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "detections";
DROP SEQUENCE IF EXISTS detections_id_seq;
CREATE SEQUENCE detections_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."detections" (
    "det_id" integer DEFAULT nextval('detections_id_seq') NOT NULL,
    "geom" geometry(Point,4326),
    "det_lp" character varying,
    "det_lat" double precision,
    "det_lng" double precision,
    "det_detcargpsalat" double precision,
    "det_detcargpsalng" double precision,
    "det_detcargpsblat" double precision,
    "det_detcargpsblng" double precision,
    "det_parkingtype" character varying,
    "det_lr" character varying,
    "det_fr" character varying,
    "det_measid" character varying,
    "det_street" character varying,
    "det_odometerm" integer,
    "det_utc" timestamptz,
    "gps_id" integer,
    CONSTRAINT "detections_pkey" PRIMARY KEY ("det_id")
) WITH (oids = false);

CREATE INDEX "det_idx_utc" ON "public"."detections" USING btree ("det_utc");

CREATE INDEX "fki_det_fkey_gps" ON "public"."detections" USING btree ("gps_id");

CREATE INDEX "sidx_detections_geom" ON "public"."detections" USING btree ("geom");


DROP TABLE IF EXISTS "discussion";
DROP SEQUENCE IF EXISTS discussion_dis_id_seq;
CREATE SEQUENCE discussion_dis_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."discussion" (
    "dis_id" integer DEFAULT nextval('discussion_dis_id_seq') NOT NULL,
    "dis_identificator" character varying(31) NOT NULL,
    "use_id" integer,
    "dis_email" character varying(255),
    "dis_message" character varying(2047) NOT NULL,
    "dis_reply" integer DEFAULT '0',
    "dis_authorized_by" integer,
    "dis_datetimeinsert" timestamp NOT NULL,
    CONSTRAINT "discussion_pkey" PRIMARY KEY ("dis_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "files";
DROP SEQUENCE IF EXISTS files_fil_id_seq;
CREATE SEQUENCE files_fil_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."files" (
    "fil_id" integer DEFAULT nextval('files_fil_id_seq') NOT NULL,
    "fil_name" character varying(255) DEFAULT '' NOT NULL,
    "fil_path" character varying(255) DEFAULT '' NOT NULL,
    "fil_storagename" character varying(255) DEFAULT '' NOT NULL,
    "fil_ext" character varying(31) DEFAULT '' NOT NULL,
    "typ_id" integer DEFAULT '0' NOT NULL,
    "fil_ref_table" character varying(255),
    "fil_ref_type" character varying(31),
    CONSTRAINT "files_pkey" PRIMARY KEY ("fil_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "gps";
DROP SEQUENCE IF EXISTS gps2_id_seq;
CREATE SEQUENCE gps2_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."gps" (
    "gps_id" integer DEFAULT nextval('gps2_id_seq') NOT NULL,
    "geom" geometry(Point,4326),
    "gps_lat" double precision,
    "gps_lng" double precision,
    "gps_heading" double precision,
    "gps_speedkmperhour" double precision,
    "gps_odometerm" double precision,
    "gps_utc" timestamptz,
    "gps_segmentsdistance" double precision,
    "seg_id" integer,
    "cam_id" integer,
    CONSTRAINT "gps2_pkey" PRIMARY KEY ("gps_id")
) WITH (oids = false);

CREATE INDEX "fki_gps_fkey_cam" ON "public"."gps" USING btree ("cam_id");

CREATE INDEX "gps_idx_segid" ON "public"."gps" USING btree ("seg_id");

CREATE INDEX "gps_idx_utc" ON "public"."gps" USING btree ("gps_utc");

CREATE INDEX "sidx_gps2_geom" ON "public"."gps" USING btree ("geom");


DROP TABLE IF EXISTS "notes";
DROP SEQUENCE IF EXISTS notes_not_id_seq;
CREATE SEQUENCE notes_not_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."notes" (
    "not_id" integer DEFAULT nextval('notes_not_id_seq') NOT NULL,
    "not_page" character varying(2047) NOT NULL,
    "not_note" character varying(4095) NOT NULL,
    "use_id" integer NOT NULL,
    CONSTRAINT "notes_pkey" PRIMARY KEY ("not_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "projects";
DROP SEQUENCE IF EXISTS projects_pro_id_seq;
CREATE SEQUENCE projects_pro_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."projects" (
    "pro_id" integer DEFAULT nextval('projects_pro_id_seq') NOT NULL,
    "pro_name" character varying,
    "pro_note" character varying,
    "pro_datetimeinsert" timestamp,
    CONSTRAINT "projects_pkey" PRIMARY KEY ("pro_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "queries";
DROP SEQUENCE IF EXISTS queries_que_id_seq;
CREATE SEQUENCE queries_que_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."queries" (
    "que_id" integer DEFAULT nextval('queries_que_id_seq') NOT NULL,
    "que_name" character varying(511) NOT NULL,
    "que_query" text NOT NULL,
    "que_note" character varying(2047),
    "que_status" integer,
    "que_datetimeinsert" timestamp NOT NULL,
    CONSTRAINT "queries_pkey" PRIMARY KEY ("que_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "segments";
DROP SEQUENCE IF EXISTS segments_id_seq;
CREATE SEQUENCE segments_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 9223372036854775807 CACHE 1;

CREATE TABLE "public"."segments" (
    "seg_id" integer DEFAULT nextval('segments_id_seq') NOT NULL,
    "geom" geometry(MultiLineString,5514),
    "fcc" bigint,
    "road_id" bigint,
    "on" character varying(48),
    "df" bigint,
    "fw" bigint,
    "sl" bigint,
    "fy" bigint,
    "ds" bigint,
    "fc" bigint,
    "rn" character varying(7),
    "rne" character varying(16),
    "ring" character varying(6),
    "toll_road" bigint,
    "toll" bigint,
    "urban" bigint,
    "oc_admin8" character varying(6),
    "oc_admin9" character varying(6),
    "level_b" bigint,
    "level_m" bigint,
    "level_e" bigint,
    "bt" bigint,
    "code_str" bigint,
    "nc" bigint,
    "cst" character varying(5),
    "oneway" character varying(2),
    "meter" double precision,
    "winter" bigint,
    "dual" bigint,
    "cis_useku" character varying(20),
    "tahy_komun" character varying(60),
    "pap_vet" bigint,
    "trida_komu" bigint,
    "pr_stan1" bigint,
    "pr_stan2" bigint,
    "us_stan1" bigint,
    "us_stan2" bigint,
    "prepocet" bigint,
    "cislo_sil" character varying(10),
    "oc_urbanu" character varying(6),
    "waste" bigint,
    "td" bigint,
    "kod_r" bigint,
    "dopo_souce" character varying(11),
    "odpo_souce" character varying(10),
    "noc_soucet" character varying(10),
    "odpo_neleg" character varying(10),
    "odpo_volne" bigint,
    "dopo_neleg" character varying(10),
    "dopo_volne" character varying(10),
    "noc_neleg" character varying(10),
    "noc_volne" bigint,
    "seg_coordinates" text,
    "pro_id" integer,
    "seg_used" smallint,
    CONSTRAINT "segments_pkey" PRIMARY KEY ("seg_id")
) WITH (oids = false);

CREATE INDEX "seg_proid" ON "public"."segments" USING btree ("pro_id");

CREATE INDEX "segments_idex_used" ON "public"."segments" USING btree ("seg_used");

CREATE INDEX "sidx_segments_geom" ON "public"."segments" USING btree ("geom");


DROP TABLE IF EXISTS "users";
DROP SEQUENCE IF EXISTS users_use_id_seq;
CREATE SEQUENCE users_use_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users" (
    "use_id" integer DEFAULT nextval('users_use_id_seq') NOT NULL,
    "use_email" character varying(255) DEFAULT '' NOT NULL,
    "use_passhash" character varying(511) DEFAULT '' NOT NULL,
    "use_name" character varying(255) DEFAULT '',
    "use_phone" character varying(31) DEFAULT '',
    "use_token_email" character varying(255),
    "use_token_expiration_email" timestamp,
    "use_email_verifed" integer,
    "use_token_password" character varying(255),
    "use_token_expiration_password" timestamp,
    "use_terms_agreement" integer DEFAULT '0' NOT NULL,
    "use_tfa_enabled" integer DEFAULT '0' NOT NULL,
    "use_tfa_secret" character varying(127),
    "use_role" character varying(31),
    "use_active" integer NOT NULL,
    "use_first_login" integer NOT NULL,
    "use_discussion_authorized" integer,
    "fil_id" integer,
    CONSTRAINT "users_pkey" PRIMARY KEY ("use_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "users_configurations";
DROP SEQUENCE IF EXISTS users_configurations_usc_id_seq;
CREATE SEQUENCE users_configurations_usc_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users_configurations" (
    "usc_id" integer DEFAULT nextval('users_configurations_usc_id_seq') NOT NULL,
    "use_id" integer NOT NULL,
    "usc_type" character varying(63) NOT NULL,
    "usc_value" character varying(63) NOT NULL,
    "usc_datetimeinsert" timestamp NOT NULL,
    CONSTRAINT "users_configurations_pkey" PRIMARY KEY ("usc_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "users_otp";
DROP SEQUENCE IF EXISTS users_otp_uso_id_seq;
CREATE SEQUENCE users_otp_uso_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users_otp" (
    "uso_id" integer DEFAULT nextval('users_otp_uso_id_seq') NOT NULL,
    "uso_passhash" character varying(511) NOT NULL,
    "uso_valid_to" timestamp NOT NULL,
    "uso_enabled" integer NOT NULL,
    "use_id" integer NOT NULL,
    CONSTRAINT "users_otp_pkey" PRIMARY KEY ("uso_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "users_roles";
DROP SEQUENCE IF EXISTS users_roles_usr_id_seq;
CREATE SEQUENCE users_roles_usr_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."users_roles" (
    "usr_id" integer DEFAULT nextval('users_roles_usr_id_seq') NOT NULL,
    "use_id" integer NOT NULL,
    "usr_role" character varying(31) NOT NULL,
    CONSTRAINT "users_roles_pkey" PRIMARY KEY ("usr_id")
) WITH (oids = false);


DROP TABLE IF EXISTS "videos";
DROP SEQUENCE IF EXISTS videos_vid_id_seq;
CREATE SEQUENCE videos_vid_id_seq INCREMENT 1 MINVALUE 1 MAXVALUE 2147483647 CACHE 1;

CREATE TABLE "public"."videos" (
    "vid_id" integer DEFAULT nextval('videos_vid_id_seq') NOT NULL,
    "vid_name" character varying NOT NULL,
    "vid_start" timestamp NOT NULL,
    "vid_end" timestamp NOT NULL,
    "vid_playtime" real NOT NULL,
    "vid_side" character varying NOT NULL,
    "pro_id" integer NOT NULL,
    CONSTRAINT "videos_pkey" PRIMARY KEY ("vid_id")
) WITH (oids = false);


ALTER TABLE ONLY "public"."campaigns" ADD CONSTRAINT "cam_fkey_pro" FOREIGN KEY (pro_id) REFERENCES projects(pro_id) NOT VALID NOT DEFERRABLE;

ALTER TABLE ONLY "public"."campaigns_segments" ADD CONSTRAINT "campaigns_segments_use_id_fkey" FOREIGN KEY (use_id) REFERENCES users(use_id) NOT DEFERRABLE;
ALTER TABLE ONLY "public"."campaigns_segments" ADD CONSTRAINT "cas_fkey_cam" FOREIGN KEY (cam_id) REFERENCES campaigns(cam_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;
ALTER TABLE ONLY "public"."campaigns_segments" ADD CONSTRAINT "cas_fkey_seg" FOREIGN KEY (seg_id) REFERENCES segments(seg_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;

ALTER TABLE ONLY "public"."contactform" ADD CONSTRAINT "users_ibfk_1" FOREIGN KEY (use_id) REFERENCES users(use_id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."detections" ADD CONSTRAINT "det_fkey_gps" FOREIGN KEY (gps_id) REFERENCES gps(gps_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;

ALTER TABLE ONLY "public"."gps" ADD CONSTRAINT "gps_fkey_cam" FOREIGN KEY (cam_id) REFERENCES campaigns(cam_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;
ALTER TABLE ONLY "public"."gps" ADD CONSTRAINT "gps_fkey_seg" FOREIGN KEY (seg_id) REFERENCES segments(seg_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;

ALTER TABLE ONLY "public"."notes" ADD CONSTRAINT "notes_ibfk_1" FOREIGN KEY (use_id) REFERENCES users(use_id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."segments" ADD CONSTRAINT "seg_fkey_pro" FOREIGN KEY (pro_id) REFERENCES projects(pro_id) ON UPDATE CASCADE NOT VALID NOT DEFERRABLE;

ALTER TABLE ONLY "public"."users_configurations" ADD CONSTRAINT "users_configurations_ibfk_1" FOREIGN KEY (use_id) REFERENCES users(use_id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."users_roles" ADD CONSTRAINT "users_roles_ibfk_1" FOREIGN KEY (use_id) REFERENCES users(use_id) NOT DEFERRABLE;

ALTER TABLE ONLY "public"."videos" ADD CONSTRAINT "videos_pro_id_fkey" FOREIGN KEY (pro_id) REFERENCES projects(pro_id) NOT DEFERRABLE;

-- 2024-01-31 14:57:24.105064+01

