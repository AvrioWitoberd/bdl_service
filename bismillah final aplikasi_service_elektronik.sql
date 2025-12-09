--
-- PostgreSQL database dump
--

\restrict W9NcCyGPcDFSbbH8zcXyRoY1GlBzjVsUle5vRfyaGiuDaN6BnWTPd89lffLEIgo

-- Dumped from database version 18.0
-- Dumped by pg_dump version 18.0

-- Started on 2025-12-08 22:10:33

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 5221 (class 1262 OID 17121)
-- Name: aplikasi_service_elektronik; Type: DATABASE; Schema: -; Owner: postgres
--

CREATE DATABASE aplikasi_service_elektronik WITH TEMPLATE = template0 ENCODING = 'UTF8' LOCALE_PROVIDER = libc LOCALE = 'English_Indonesia.1252';


ALTER DATABASE aplikasi_service_elektronik OWNER TO postgres;

\unrestrict W9NcCyGPcDFSbbH8zcXyRoY1GlBzjVsUle5vRfyaGiuDaN6BnWTPd89lffLEIgo
\connect aplikasi_service_elektronik
\restrict W9NcCyGPcDFSbbH8zcXyRoY1GlBzjVsUle5vRfyaGiuDaN6BnWTPd89lffLEIgo

SET statement_timeout = 0;
SET lock_timeout = 0;
SET idle_in_transaction_session_timeout = 0;
SET transaction_timeout = 0;
SET client_encoding = 'UTF8';
SET standard_conforming_strings = on;
SELECT pg_catalog.set_config('search_path', '', false);
SET check_function_bodies = false;
SET xmloption = content;
SET client_min_messages = warning;
SET row_security = off;

--
-- TOC entry 2 (class 3079 OID 17509)
-- Name: pg_trgm; Type: EXTENSION; Schema: -; Owner: -
--

CREATE EXTENSION IF NOT EXISTS pg_trgm WITH SCHEMA public;


--
-- TOC entry 5222 (class 0 OID 0)
-- Dependencies: 2
-- Name: EXTENSION pg_trgm; Type: COMMENT; Schema: -; Owner: 
--

COMMENT ON EXTENSION pg_trgm IS 'text similarity measurement and index searching based on trigrams';


--
-- TOC entry 289 (class 1255 OID 17656)
-- Name: fn_update_status_selesai(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.fn_update_status_selesai() RETURNS trigger
    LANGUAGE plpgsql
    AS $$
begin
    if new.tanggal_selesai is not null then
        new.id_status = 6006;  -- status 'Selesai Diperbaiki'
    end if;
    return new;
end;
$$;


ALTER FUNCTION public.fn_update_status_selesai() OWNER TO postgres;

--
-- TOC entry 273 (class 1255 OID 17590)
-- Name: get_daftar_service(); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_daftar_service() RETURNS TABLE(id_service integer, nama_pelanggan character varying, nama_teknisi character varying, tanggal_masuk date, tanggal_selesai date, biaya_service numeric)
    LANGUAGE plpgsql
    AS $$
BEGIN
    RETURN QUERY
    SELECT 
        s.id_service,
        p.nama AS nama_pelanggan,
        t.nama_teknisi,
        s.tanggal_masuk,
        s.tanggal_selesai,
        s.biaya_service
    FROM service s
    JOIN pelanggan p ON s.id_pelanggan = p.id_pelanggan
    JOIN teknisi t ON s.id_teknisi = t.id_teknisi
    ORDER BY s.tanggal_masuk DESC;
END;
$$;


ALTER FUNCTION public.get_daftar_service() OWNER TO postgres;

--
-- TOC entry 274 (class 1255 OID 17591)
-- Name: get_total_biaya_service(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.get_total_biaya_service(p_id_service integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
DECLARE
    v_biaya_service NUMERIC;
    v_biaya_sparepart NUMERIC;
BEGIN
    SELECT COALESCE(biaya_service, 0)
    INTO v_biaya_service
    FROM service
    WHERE id_service = p_id_service;

    SELECT COALESCE(harga, 0)
    INTO v_biaya_sparepart
    FROM sparepart
    WHERE id_sparepart = (
        SELECT id_sparepart FROM service WHERE id_service = p_id_service
    );

    RETURN v_biaya_service + v_biaya_sparepart;
END;
$$;


ALTER FUNCTION public.get_total_biaya_service(p_id_service integer) OWNER TO postgres;

--
-- TOC entry 276 (class 1255 OID 17647)
-- Name: hitung_biaya_service(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.hitung_biaya_service(id_input integer) RETURNS numeric
    LANGUAGE plpgsql
    AS $$
declare
    total numeric;
begin
    select biaya_service
    into total
    from service
    where id_service = id_input;

    return coalesce(total, 0);
end;
$$;


ALTER FUNCTION public.hitung_biaya_service(id_input integer) OWNER TO postgres;

--
-- TOC entry 277 (class 1255 OID 17648)
-- Name: jumlah_service_teknisi(integer); Type: FUNCTION; Schema: public; Owner: postgres
--

CREATE FUNCTION public.jumlah_service_teknisi(id_input integer) RETURNS integer
    LANGUAGE plpgsql
    AS $$
declare
    jumlah integer;
begin
    select count(*)
    into jumlah
    from service
    where id_teknisi = id_input;

    return jumlah;
end;
$$;


ALTER FUNCTION public.jumlah_service_teknisi(id_input integer) OWNER TO postgres;

--
-- TOC entry 282 (class 1255 OID 17649)
-- Name: proses_service_dan_pembayaran(integer, numeric); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.proses_service_dan_pembayaran(IN p_id_service integer, IN p_total_bayar numeric)
    LANGUAGE plpgsql
    AS $$
begin
    -- update service: tandai selesai
    update service
    set tanggal_selesai = current_date
    where id_service = p_id_service;

    -- jika pembayaran belum ada → buat baru
    if not exists (
        select 1 from pembayaran where id_service = p_id_service
    ) then
        insert into pembayaran(
            id_service, tanggal_bayar, total_bayar, status_bayar
        ) values (
            p_id_service, current_date, p_total_bayar, 'lunas'
        );
    else
        -- kalau sudah ada → update total bayar
        update pembayaran
        set total_bayar = p_total_bayar,
            tanggal_bayar = current_date,
            status_bayar = 'lunas'
        where id_service = p_id_service;
    end if;

end $$;


ALTER PROCEDURE public.proses_service_dan_pembayaran(IN p_id_service integer, IN p_total_bayar numeric) OWNER TO postgres;

--
-- TOC entry 275 (class 1255 OID 17592)
-- Name: update_status_lunas(integer); Type: PROCEDURE; Schema: public; Owner: postgres
--

CREATE PROCEDURE public.update_status_lunas(IN p_id_pembayaran integer)
    LANGUAGE plpgsql
    AS $$
BEGIN
    UPDATE pembayaran
    SET status_bayar = 'Lunas',
        tanggal_bayar = CURRENT_DATE
    WHERE id_pembayaran = p_id_pembayaran;

    RAISE NOTICE 'Status pembayaran % telah diubah menjadi Lunas', p_id_pembayaran;
END;
$$;


ALTER PROCEDURE public.update_status_lunas(IN p_id_pembayaran integer) OWNER TO postgres;

--
-- TOC entry 227 (class 1259 OID 17209)
-- Name: seq_admin; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_admin
    START WITH 8001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_admin OWNER TO postgres;

SET default_tablespace = '';

SET default_table_access_method = heap;

--
-- TOC entry 231 (class 1259 OID 17257)
-- Name: admin; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.admin (
    id_admin integer DEFAULT nextval('public.seq_admin'::regclass) NOT NULL,
    username character varying(50) NOT NULL,
    password text CONSTRAINT admin_password_hash_not_null NOT NULL,
    nama_admin character varying(120),
    email character varying(150),
    tanggal_dibuat date DEFAULT CURRENT_DATE
);


ALTER TABLE public.admin OWNER TO postgres;

--
-- TOC entry 226 (class 1259 OID 17208)
-- Name: seq_pembayaran; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_pembayaran
    START WITH 7001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_pembayaran OWNER TO postgres;

--
-- TOC entry 235 (class 1259 OID 17331)
-- Name: pembayaran; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pembayaran (
    id_pembayaran integer DEFAULT nextval('public.seq_pembayaran'::regclass) NOT NULL,
    id_service integer NOT NULL,
    tanggal_bayar date DEFAULT CURRENT_DATE,
    metode_bayar character varying(50) DEFAULT 'Tunai'::character varying,
    total_bayar numeric(10,2) NOT NULL,
    diskon numeric(10,2) DEFAULT 0,
    status_bayar character varying(30) DEFAULT 'Belum Lunas'::character varying
);


ALTER TABLE public.pembayaran OWNER TO postgres;

--
-- TOC entry 223 (class 1259 OID 17205)
-- Name: seq_service; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_service
    START WITH 4001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_service OWNER TO postgres;

--
-- TOC entry 222 (class 1259 OID 17204)
-- Name: seq_teknisi; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_teknisi
    START WITH 3001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_teknisi OWNER TO postgres;

--
-- TOC entry 233 (class 1259 OID 17285)
-- Name: service; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.service (
    id_service integer DEFAULT nextval('public.seq_service'::regclass) NOT NULL,
    id_perangkat integer NOT NULL,
    id_teknisi integer,
    id_admin integer NOT NULL,
    id_status integer NOT NULL,
    tanggal_masuk date DEFAULT CURRENT_DATE,
    tanggal_selesai date,
    keluhan text NOT NULL,
    biaya_service numeric(12,2) DEFAULT 0,
    keterangan text,
    catatan_internal text,
    sparepart_digunakan text,
    id_sparepart integer,
    id_pelanggan integer
);


ALTER TABLE public.service OWNER TO postgres;

--
-- TOC entry 230 (class 1259 OID 17244)
-- Name: teknisi; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.teknisi (
    id_teknisi integer DEFAULT nextval('public.seq_teknisi'::regclass) NOT NULL,
    nama_teknisi character varying(120) NOT NULL,
    keahlian character varying(100),
    no_hp character varying(20),
    email character varying(150),
    status_aktif boolean DEFAULT true,
    password character varying(255)
);


ALTER TABLE public.teknisi OWNER TO postgres;

--
-- TOC entry 236 (class 1259 OID 17593)
-- Name: mv_kinerja_teknisi; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_kinerja_teknisi AS
 SELECT t.id_teknisi,
    t.nama_teknisi,
    count(s.id_service) AS total_service,
    COALESCE(avg(s.biaya_service), (0)::numeric) AS rata_biaya_service,
    COALESCE(sum(pb.total_bayar), (0)::numeric) AS total_pendapatan
   FROM ((public.teknisi t
     LEFT JOIN public.service s ON ((t.id_teknisi = s.id_teknisi)))
     LEFT JOIN public.pembayaran pb ON ((s.id_service = pb.id_service)))
  GROUP BY t.id_teknisi, t.nama_teknisi
  ORDER BY (count(s.id_service)) DESC
  WITH NO DATA;


ALTER MATERIALIZED VIEW public.mv_kinerja_teknisi OWNER TO postgres;

--
-- TOC entry 241 (class 1259 OID 17631)
-- Name: mv_pendapatan_bulanan; Type: MATERIALIZED VIEW; Schema: public; Owner: postgres
--

CREATE MATERIALIZED VIEW public.mv_pendapatan_bulanan AS
 SELECT date_trunc('month'::text, (tanggal_bayar)::timestamp with time zone) AS bulan,
    sum(total_bayar) AS total_pendapatan,
    count(id_pembayaran) AS jumlah_transaksi
   FROM public.pembayaran pb
  WHERE ((status_bayar)::text = 'lunas'::text)
  GROUP BY (date_trunc('month'::text, (tanggal_bayar)::timestamp with time zone))
  ORDER BY (date_trunc('month'::text, (tanggal_bayar)::timestamp with time zone))
  WITH NO DATA;


ALTER MATERIALIZED VIEW public.mv_pendapatan_bulanan OWNER TO postgres;

--
-- TOC entry 220 (class 1259 OID 17202)
-- Name: seq_pelanggan; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_pelanggan
    START WITH 1001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_pelanggan OWNER TO postgres;

--
-- TOC entry 228 (class 1259 OID 17211)
-- Name: pelanggan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.pelanggan (
    id_pelanggan integer DEFAULT nextval('public.seq_pelanggan'::regclass) NOT NULL,
    nama character varying(120) NOT NULL,
    no_hp character varying(20) NOT NULL,
    alamat text,
    email character varying(150),
    tanggal_daftar date DEFAULT CURRENT_DATE,
    password character varying(255)
);


ALTER TABLE public.pelanggan OWNER TO postgres;

--
-- TOC entry 221 (class 1259 OID 17203)
-- Name: seq_perangkat; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_perangkat
    START WITH 2001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_perangkat OWNER TO postgres;

--
-- TOC entry 229 (class 1259 OID 17227)
-- Name: perangkat; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.perangkat (
    id_perangkat integer DEFAULT nextval('public.seq_perangkat'::regclass) NOT NULL,
    id_pelanggan integer NOT NULL,
    nama_perangkat character varying(120) NOT NULL,
    jenis_perangkat character varying(60),
    merek character varying(60),
    nomor_seri character varying(100),
    tanggal_input date DEFAULT CURRENT_DATE,
    masa_garansi interval DEFAULT '1 year'::interval
);


ALTER TABLE public.perangkat OWNER TO postgres;

--
-- TOC entry 224 (class 1259 OID 17206)
-- Name: seq_sparepart; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_sparepart
    START WITH 5001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_sparepart OWNER TO postgres;

--
-- TOC entry 225 (class 1259 OID 17207)
-- Name: seq_status_perbaikan; Type: SEQUENCE; Schema: public; Owner: postgres
--

CREATE SEQUENCE public.seq_status_perbaikan
    START WITH 6001
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1;


ALTER SEQUENCE public.seq_status_perbaikan OWNER TO postgres;

--
-- TOC entry 234 (class 1259 OID 17320)
-- Name: sparepart; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.sparepart (
    id_sparepart integer DEFAULT nextval('public.seq_sparepart'::regclass) NOT NULL,
    nama_sparepart character varying(120) NOT NULL,
    stok integer DEFAULT 0,
    harga numeric(12,2) NOT NULL,
    merek character varying(60),
    tanggal_update date DEFAULT CURRENT_DATE,
    id_service integer
);


ALTER TABLE public.sparepart OWNER TO postgres;

--
-- TOC entry 232 (class 1259 OID 17273)
-- Name: status_perbaikan; Type: TABLE; Schema: public; Owner: postgres
--

CREATE TABLE public.status_perbaikan (
    id_status integer DEFAULT nextval('public.seq_status_perbaikan'::regclass) NOT NULL,
    nama_status character varying(60) NOT NULL,
    deskripsi text
);


ALTER TABLE public.status_perbaikan OWNER TO postgres;

--
-- TOC entry 240 (class 1259 OID 17626)
-- Name: view_laporan_service; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.view_laporan_service AS
 SELECT s.id_service,
    p.nama AS nama_pelanggan,
    pr.nama_perangkat,
    t.nama_teknisi,
    s.tanggal_masuk,
    s.tanggal_selesai,
    s.biaya_service,
    s.keterangan,
    pb.total_bayar,
    pb.status_bayar
   FROM ((((public.service s
     JOIN public.pelanggan p ON ((s.id_pelanggan = p.id_pelanggan)))
     JOIN public.perangkat pr ON ((s.id_perangkat = pr.id_perangkat)))
     JOIN public.teknisi t ON ((s.id_teknisi = t.id_teknisi)))
     LEFT JOIN public.pembayaran pb ON ((s.id_service = pb.id_service)));


ALTER VIEW public.view_laporan_service OWNER TO postgres;

--
-- TOC entry 239 (class 1259 OID 17622)
-- Name: view_service_selesai; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.view_service_selesai AS
 SELECT id_service,
    id_pelanggan,
    id_teknisi,
    tanggal_masuk,
    tanggal_selesai,
    biaya_service,
    keterangan
   FROM public.service
  WHERE (tanggal_selesai IS NOT NULL);


ALTER VIEW public.view_service_selesai OWNER TO postgres;

--
-- TOC entry 237 (class 1259 OID 17600)
-- Name: vw_laporan_service_lengkap; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_laporan_service_lengkap AS
 SELECT s.id_service,
    p.nama AS nama_pelanggan,
    pr.merek AS merek_perangkat,
    t.nama_teknisi,
    st.nama_status AS status_service,
    s.tanggal_masuk,
    s.tanggal_selesai,
    s.keluhan,
    s.biaya_service,
    pb.total_bayar,
    pb.status_bayar,
    a.nama_admin AS admin_penanggung_jawab
   FROM ((((((public.service s
     JOIN public.pelanggan p ON ((s.id_pelanggan = p.id_pelanggan)))
     JOIN public.perangkat pr ON ((s.id_perangkat = pr.id_perangkat)))
     JOIN public.teknisi t ON ((s.id_teknisi = t.id_teknisi)))
     JOIN public.admin a ON ((s.id_admin = a.id_admin)))
     LEFT JOIN public.pembayaran pb ON ((s.id_service = pb.id_service)))
     JOIN public.status_perbaikan st ON ((s.id_status = st.id_status)));


ALTER VIEW public.vw_laporan_service_lengkap OWNER TO postgres;

--
-- TOC entry 238 (class 1259 OID 17605)
-- Name: vw_pembayaran_sederhana; Type: VIEW; Schema: public; Owner: postgres
--

CREATE VIEW public.vw_pembayaran_sederhana AS
 SELECT id_pembayaran,
    tanggal_bayar,
    metode_bayar,
    total_bayar,
    status_bayar
   FROM public.pembayaran;


ALTER VIEW public.vw_pembayaran_sederhana OWNER TO postgres;

--
-- TOC entry 5209 (class 0 OID 17257)
-- Dependencies: 231
-- Data for Name: admin; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.admin VALUES (8001, 'admin_andi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Andi Saputra', 'adminandi@gmail.com', '2025-08-30');
INSERT INTO public.admin VALUES (8002, 'admin_budi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Budi Rahman', 'adminbudi@gmail.com', '2025-09-01');
INSERT INTO public.admin VALUES (8003, 'admin_citra', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Citra Lestari', 'admincitra@gmail.com', '2025-09-04');
INSERT INTO public.admin VALUES (8004, 'admin_dedi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Dedi Prakoso', 'admindedi@gmail.com', '2025-09-06');
INSERT INTO public.admin VALUES (8005, 'admin_eka', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Eka Putri', 'admineka@gmail.com', '2025-09-09');
INSERT INTO public.admin VALUES (8006, 'admin_fajar', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Fajar Nugraha', 'adminfajar@gmail.com', '2025-09-11');
INSERT INTO public.admin VALUES (8007, 'admin_gilang', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Gilang Santoso', 'admingilang@gmail.com', '2025-09-14');
INSERT INTO public.admin VALUES (8008, 'admin_hani', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Hani Marlina', 'adminhani@gmail.com', '2025-09-16');
INSERT INTO public.admin VALUES (8009, 'admin_irfan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Irfan Kurniawan', 'adminirfan@gmail.com', '2025-09-19');
INSERT INTO public.admin VALUES (8010, 'admin_joko', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Joko Widodo', 'adminjoko@gmail.com', '2025-09-21');
INSERT INTO public.admin VALUES (8011, 'admin_kiki', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Kiki Anggraini', 'adminkiki@gmail.com', '2025-09-24');
INSERT INTO public.admin VALUES (8012, 'admin_lutfi', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Lutfi Hidayat', 'adminlutfi@gmail.com', '2025-09-27');
INSERT INTO public.admin VALUES (8013, 'admin_maya', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Maya Sari', 'adminmaya@gmail.com', '2025-09-29');
INSERT INTO public.admin VALUES (8014, 'admin_nanda', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Nanda Prakoso', 'adminnanda@gmail.com', '2025-10-02');
INSERT INTO public.admin VALUES (8015, 'admin_oka', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Oka Wijaya', 'adminoka@gmail.com', '2025-10-04');
INSERT INTO public.admin VALUES (8016, 'admin_putri', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Putri Ayuningtyas', 'adminputri@gmail.com', '2025-10-07');
INSERT INTO public.admin VALUES (8017, 'admin_rian', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Rian Firmansyah', 'adminrian@gmail.com', '2025-10-09');
INSERT INTO public.admin VALUES (8018, 'admin_sari', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Sari Indah', 'adminsari@gmail.com', '2025-10-11');
INSERT INTO public.admin VALUES (8019, 'admin_taufik', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Taufik Hidayat', 'admintaufik@gmail.com', '2025-10-14');
INSERT INTO public.admin VALUES (8020, 'admin_wulan', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Wulan Dewi', 'adminwulan@gmail.com', '2025-10-19');


--
-- TOC entry 5206 (class 0 OID 17211)
-- Dependencies: 228
-- Data for Name: pelanggan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pelanggan VALUES (1001, 'Andi Pratama', '081234000001', 'Malang', 'andipratama01@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1002, 'Budi Santoso', '081234000002', 'Malang', 'budisantoso02@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1003, 'Citra Dewi', '081234000003', 'Batu', 'citradewi03@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1004, 'Dimas Haryanto', '081234000004', 'Malang', 'dimasharyanto04@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1005, 'Eka Sari', '081234000005', 'Kepanjen', 'ekasari05@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1006, 'Farhan Nugraha', '081234000006', 'Blitar', 'farhann06@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1007, 'Gita Lestari', '081234000007', 'Malang', 'gitalestari07@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1008, 'Hadi Firmansyah', '081234000008', 'Batu', 'hadifirmansyah08@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1009, 'Intan Puspita', '081234000009', 'Dau', 'intanpuspita09@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1010, 'Joko Prabowo', '081234000010', 'Malang', 'jokoprabowo10@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1011, 'Kartika Ayu', '081234000011', 'Malang', 'kartikaayu11@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1012, 'Lutfi Ramadhan', '081234000012', 'Kepanjen', 'lutfiramadhan12@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1013, 'Mega Salsabila', '081234000013', 'Batu', 'megasalsabila13@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1014, 'Nanda Saputra', '081234000014', 'Malang', 'nandasaputra14@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1015, 'Oktavia Rahma', '081234000015', 'Malang', 'oktaviarahma15@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1016, 'Putra Wijaya', '081234000016', 'Turen', 'putrawijaya16@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1017, 'Qori Maulida', '081234000017', 'Malang', 'qorimaulida17@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1018, 'Rama Dwi Kurnia', '081234000018', 'Dampit', 'ramadwikurnia18@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1019, 'Siska Marlina', '081234000019', 'Malang', 'siskamarlina19@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1020, 'Taufik Hidayat', '081234000020', 'Batu', 'taufikhidayat20@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1021, 'Umi Salma', '081234000021', 'Malang', 'umisalma21@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1022, 'Vino Pratama', '081234000022', 'Malang', 'vinopratama22@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1023, 'Wulan Kurniasih', '081234000023', 'Pagelaran', 'wulankurniasih23@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1024, 'Xavier Maulana', '081234000024', 'Malang', 'xaviermaulana24@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1025, 'Yoga Prasasti', '081234000025', 'Blitar', 'yogaprasasti25@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1026, 'Zahra Fitriani', '081234000026', 'Malang', 'zahrafitriani26@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1027, 'Anya Rahmawati', '081234000027', 'Samarinda', 'anyarahmawati27@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1028, 'Berlian Putri', '081234000028', 'Malang', 'berlianputri28@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1029, 'Chandra Adi', '081234000029', 'Tulungagung', 'chandraadi29@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1030, 'Dewangga Surya', '081234000030', 'Malang', 'dewanggasurya30@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1031, 'Erwin Septian', '081234000031', 'Batu', 'erwinseptian31@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1032, 'Fauzi Ridwan', '081234000032', 'Malang', 'fauziridwan32@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1033, 'Galuh Rahadian', '081234000033', 'Malang', 'galuhrahadian33@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1034, 'Herlina Mutiara', '081234000034', 'Dau', 'herlinamutiara34@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1035, 'Ilham Kurniawan', '081234000035', 'Malang', 'ilhamkurniawan35@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1036, 'Jihan Fadila', '081234000036', 'Batu', 'jihanfadila36@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1037, 'Kenzu Mahendra', '081234000037', 'Malang', 'kenzumahendra37@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1039, 'Miko Anargya', '081234000039', 'Malang', 'mikoanargya39@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1038, 'Larisa Ayunda', '081234000038', 'Kediri', 'larisaayunda38@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');
INSERT INTO public.pelanggan VALUES (1040, 'Nayla Rahmatika', '081234000040', 'Malang', 'naylarahmatika40@gmail.com', '2025-10-29', '$2y$10$8u4q7ZzL8d0kYwv5x6Y2n8JlF3nZP4i9COg9q4Yk9972z9cYd2d3kqO');


--
-- TOC entry 5213 (class 0 OID 17331)
-- Dependencies: 235
-- Data for Name: pembayaran; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.pembayaran VALUES (7101, 4001, '2025-09-29', 'Tunai', 350000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7102, 4002, '2025-09-30', 'Transfer', 275000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7103, 4003, '2025-10-01', 'E-Wallet', 420000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7104, 4004, '2025-10-02', 'Tunai', 190000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7105, 4005, '2025-10-03', 'Transfer', 500000.00, 5000.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7106, 4006, '2025-10-04', 'E-Wallet', 275000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7107, 4007, '2025-10-05', 'Tunai', 150000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7109, 4009, '2025-10-07', 'E-Wallet', 310000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7110, 4010, '2025-10-08', 'Tunai', 270000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7112, 4012, '2025-10-10', 'Transfer', 265000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7113, 4013, '2025-10-11', 'Tunai', 295000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7114, 4014, '2025-10-12', 'E-Wallet', 350000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7116, 4016, '2025-10-14', 'Tunai', 380000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7117, 4017, '2025-10-15', 'Transfer', 240000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7118, 4018, '2025-10-16', 'E-Wallet', 300000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7119, 4019, '2025-10-17', 'Tunai', 275000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7120, 4020, '2025-10-18', 'Transfer', 400000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7121, 4071, '2025-10-19', 'Tunai', 230000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7122, 4072, '2025-10-20', 'E-Wallet', 210000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7123, 4073, '2025-10-21', 'Transfer', 245000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7124, 4074, '2025-10-22', 'Tunai', 265000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7125, 4075, '2025-10-23', 'Transfer', 275000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7126, 4076, '2025-10-24', 'Tunai', 220000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7127, 4077, '2025-10-25', 'E-Wallet', 235000.00, 0.00, 'Belum Lunas');
INSERT INTO public.pembayaran VALUES (7128, 4078, '2025-10-26', 'Transfer', 290000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7129, 4079, '2025-10-27', 'Tunai', 270000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7130, 4080, '2025-10-28', 'Transfer', 260000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7131, 4081, '2025-10-28', 'E-Wallet', 210000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7132, 4082, '2025-10-27', 'Transfer', 230000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7133, 4083, '2025-10-26', 'Tunai', 250000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7134, 4084, '2025-10-25', 'E-Wallet', 240000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7135, 4085, '2025-10-24', 'Transfer', 220000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7136, 4086, '2025-10-23', 'Tunai', 275000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7137, 4087, '2025-10-22', 'Transfer', 280000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7138, 4088, '2025-10-21', 'E-Wallet', 230000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7139, 4089, '2025-10-20', 'Tunai', 210000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7140, 4090, '2025-10-19', 'Transfer', 290000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7141, 4091, '2025-10-20', 'Tunai', 260000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7142, 4092, '2025-10-21', 'E-Wallet', 220000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7143, 4093, '2025-10-22', 'Transfer', 230000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7144, 4094, '2025-10-23', 'Tunai', 245000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7145, 4095, '2025-10-24', 'Transfer', 270000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7146, 4096, '2025-10-25', 'E-Wallet', 220000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7147, 4097, '2025-10-26', 'Tunai', 250000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7148, 4098, '2025-10-27', 'Transfer', 270000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7149, 4099, '2025-10-28', 'E-Wallet', 240000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7150, 4100, '2025-10-29', 'Tunai', 260000.00, 0.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7108, 4008, '2025-10-29', 'Transfer Bank', 430000.00, 50000.00, 'Lunas');
INSERT INTO public.pembayaran VALUES (7111, 4011, '2025-12-02', 'E-Wallet', 225000.00, 0.00, 'belum bayar');


--
-- TOC entry 5207 (class 0 OID 17227)
-- Dependencies: 229
-- Data for Name: perangkat; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.perangkat VALUES (2001, 1001, 'Samsung Galaxy A14', 'Smartphone', 'Samsung', 'SN-A14001', '2025-09-19', '1 year');
INSERT INTO public.perangkat VALUES (2002, 1001, 'HP Pavilion 14', 'Laptop', 'HP', 'SN-HP14A1', '2025-10-09', '1 year');
INSERT INTO public.perangkat VALUES (2003, 1002, 'Lenovo IdeaPad 3', 'Laptop', 'Lenovo', 'SN-LN3001', '2025-09-27', '1 year');
INSERT INTO public.perangkat VALUES (2004, 1002, 'Canon Pixma G2010', 'Printer', 'Canon', 'SN-CNG2010', '2025-10-11', '1 year');
INSERT INTO public.perangkat VALUES (2005, 1003, 'Asus ROG Zephyrus G14', 'Laptop', 'Asus', 'SN-ASG14', '2025-10-04', '1 year');
INSERT INTO public.perangkat VALUES (2006, 1003, 'Samsung Galaxy A34', 'Smartphone', 'Samsung', 'SN-SGA34', '2025-10-24', '1 year');
INSERT INTO public.perangkat VALUES (2007, 1004, 'Xiaomi Redmi Note 12', 'Smartphone', 'Xiaomi', 'SN-XM1201', '2025-10-09', '1 year');
INSERT INTO public.perangkat VALUES (2008, 1004, 'Xiaomi Vacuum S10', 'Smart Home', 'Xiaomi', 'SN-XMV10', '2025-10-23', '1 year');
INSERT INTO public.perangkat VALUES (2009, 1005, 'Asus ZenBook 14', 'Laptop', 'Asus', 'SN-AZ14', '2025-10-07', '1 year');
INSERT INTO public.perangkat VALUES (2010, 1005, 'Canon EOS M50', 'Kamera', 'Canon', 'SN-CN5050', '2025-10-01', '1 year');
INSERT INTO public.perangkat VALUES (2011, 1011, 'LG Smart TV 43"', 'Televisi', 'LG', 'SN-LGTV43', '2025-10-14', '1 year');
INSERT INTO public.perangkat VALUES (2012, 1011, 'Samsung Soundbar T450', 'Speaker', 'Samsung', 'SN-SMT450', '2025-10-17', '1 year');
INSERT INTO public.perangkat VALUES (2013, 1012, 'Oppo A57', 'Smartphone', 'Oppo', 'SN-OP57', '2025-10-20', '1 year');
INSERT INTO public.perangkat VALUES (2014, 1012, 'HP DeskJet 2336', 'Printer', 'HP', 'SN-HP2336', '2025-10-23', '1 year');
INSERT INTO public.perangkat VALUES (2015, 1013, 'Dell Inspiron 15', 'Laptop', 'Dell', 'SN-DI15', '2025-10-08', '1 year');
INSERT INTO public.perangkat VALUES (2016, 1014, 'Canon Pixma G3020', 'Printer', 'Canon', 'SN-CNG3020', '2025-10-16', '1 year');
INSERT INTO public.perangkat VALUES (2017, 1015, 'Vivo V25', 'Smartphone', 'Vivo', 'SN-VV25', '2025-10-11', '1 year');
INSERT INTO public.perangkat VALUES (2018, 1015, 'HP Envy 13', 'Laptop', 'HP', 'SN-HPE13', '2025-10-13', '1 year');
INSERT INTO public.perangkat VALUES (2019, 1016, 'Samsung Galaxy Tab S7', 'Tablet', 'Samsung', 'SN-SGT7', '2025-10-19', '1 year');
INSERT INTO public.perangkat VALUES (2020, 1016, 'Sony PlayStation 5', 'Konsol Game', 'Sony', 'SN-PS5001', '2025-10-24', '1 year');
INSERT INTO public.perangkat VALUES (2021, 1021, 'LG Washing Machine Turbo', 'Mesin Cuci', 'LG', 'SN-LGWMTR', '2025-10-18', '1 year');
INSERT INTO public.perangkat VALUES (2022, 1021, 'Samsung Refrigerator RT29', 'Kulkas', 'Samsung', 'SN-SMRT29', '2025-10-21', '1 year');
INSERT INTO public.perangkat VALUES (2023, 1022, 'Sharp Air Conditioner 1PK', 'AC', 'Sharp', 'SN-SHAC1PK', '2025-10-19', '1 year');
INSERT INTO public.perangkat VALUES (2024, 1022, 'Polytron Speaker PAS 79', 'Speaker', 'Polytron', 'SN-PAS79', '2025-10-26', '1 year');
INSERT INTO public.perangkat VALUES (2025, 1023, 'Panasonic Rice Cooker 1.8L', 'Rice Cooker', 'Panasonic', 'SN-PNRC18', '2025-10-23', '1 year');
INSERT INTO public.perangkat VALUES (2026, 1023, 'LG TV 55UQ8000', 'Televisi', 'LG', 'SN-LGT55U', '2025-10-27', '1 year');
INSERT INTO public.perangkat VALUES (2027, 1024, 'HP LaserJet MFP M141w', 'Printer', 'HP', 'SN-HPM141', '2025-10-20', '1 year');
INSERT INTO public.perangkat VALUES (2028, 1025, 'Acer Aspire 5', 'Laptop', 'Acer', 'SN-AA5', '2025-09-29', '1 year');
INSERT INTO public.perangkat VALUES (2029, 1026, 'Xiaomi Mi 11T Pro', 'Smartphone', 'Xiaomi', 'SN-XM11TP', '2025-10-11', '1 year');
INSERT INTO public.perangkat VALUES (2030, 1027, 'Lenovo ThinkPad X1', 'Laptop', 'Lenovo', 'SN-LTX1', '2025-10-15', '1 year');
INSERT INTO public.perangkat VALUES (2031, 1031, 'Samsung Galaxy S23', 'Smartphone', 'Samsung', 'SN-SGS23', '2025-10-21', '1 year');
INSERT INTO public.perangkat VALUES (2032, 1031, 'Sony Bravia 50"', 'Televisi', 'Sony', 'SN-SB50', '2025-10-25', '1 year');
INSERT INTO public.perangkat VALUES (2033, 1032, 'Polytron Audio PAS 28', 'Speaker', 'Polytron', 'SN-PA28', '2025-10-17', '1 year');
INSERT INTO public.perangkat VALUES (2034, 1033, 'Panasonic Microwave NN-SM33', 'Microwave', 'Panasonic', 'SN-PNN33', '2025-10-27', '1 year');
INSERT INTO public.perangkat VALUES (2035, 1033, 'LG Air Purifier 360', 'Pembersih Udara', 'LG', 'SN-LGAP36', '2025-10-26', '1 year');
INSERT INTO public.perangkat VALUES (2036, 1034, 'Samsung Monitor 24"', 'Monitor', 'Samsung', 'SN-SM24', '2025-10-22', '1 year');
INSERT INTO public.perangkat VALUES (2037, 1035, 'Acer Predator Helios 300', 'Laptop', 'Acer', 'SN-APH300', '2025-10-14', '1 year');
INSERT INTO public.perangkat VALUES (2038, 1036, 'Xiaomi Smart TV 32"', 'Televisi', 'Xiaomi', 'SN-XMTV32', '2025-10-24', '1 year');
INSERT INTO public.perangkat VALUES (2039, 1037, 'Canon EOS 200D', 'Kamera', 'Canon', 'SN-CN200D', '2025-10-09', '1 year');
INSERT INTO public.perangkat VALUES (2040, 1038, 'Dell Latitude 5420', 'Laptop', 'Dell', 'SN-DL5420', '2025-10-17', '1 year');
INSERT INTO public.perangkat VALUES (2041, 1039, 'Oppo Reno 10', 'Smartphone', 'Oppo', 'SN-OPR10', '2025-10-18', '1 year');
INSERT INTO public.perangkat VALUES (2042, 1040, 'HP EliteBook 840', 'Laptop', 'HP', 'SN-HPE840', '2025-10-20', '1 year');
INSERT INTO public.perangkat VALUES (2043, 1040, 'Sharp Refrigerator SJX197', 'Kulkas', 'Sharp', 'SN-SJX197', '2025-10-23', '1 year');
INSERT INTO public.perangkat VALUES (2044, 1040, 'LG Soundbar SP8YA', 'Speaker', 'LG', 'SN-LGSP8', '2025-10-25', '1 year');
INSERT INTO public.perangkat VALUES (2045, 1039, 'Sony PlayStation 4 Pro', 'Konsol Game', 'Sony', 'SN-PS4PRO', '2025-10-26', '1 year');
INSERT INTO public.perangkat VALUES (2046, 1038, 'Samsung Galaxy Tab A9', 'Tablet', 'Samsung', 'SN-SGTA9', '2025-10-28', '1 year');
INSERT INTO public.perangkat VALUES (2047, 1037, 'Panasonic Rice Cooker 2L', 'Rice Cooker', 'Panasonic', 'SN-PNRC2L', '2025-10-21', '1 year');
INSERT INTO public.perangkat VALUES (2048, 1036, 'Asus VivoBook 15', 'Laptop', 'Asus', 'SN-AVB15', '2025-10-17', '1 year');
INSERT INTO public.perangkat VALUES (2049, 1035, 'Xiaomi Smart Fan 2', 'Kipas Pintar', 'Xiaomi', 'SN-XMSF2', '2025-10-19', '1 year');
INSERT INTO public.perangkat VALUES (2050, 1034, 'LG Refrigerator 275L', 'Kulkas', 'LG', 'SN-LG275', '2025-10-22', '1 year');
INSERT INTO public.perangkat VALUES (2051, 1033, 'Samsung Galaxy Watch 6', 'Wearable', 'Samsung', 'SN-SGW6', '2025-10-24', '1 year');
INSERT INTO public.perangkat VALUES (2052, 1032, 'HP OfficeJet 8010', 'Printer', 'HP', 'SN-HPO8010', '2025-10-25', '1 year');
INSERT INTO public.perangkat VALUES (2053, 1031, 'Canon PowerShot G7X', 'Kamera', 'Canon', 'SN-CNG7X', '2025-10-26', '1 year');
INSERT INTO public.perangkat VALUES (2054, 1030, 'LG Smart Monitor 27"', 'Monitor', 'LG', 'SN-LGM27', '2025-10-27', '1 year');
INSERT INTO public.perangkat VALUES (2055, 1029, 'Samsung Galaxy A05s', 'Smartphone', 'Samsung', 'SN-SGA05', '2025-10-28', '1 year');


--
-- TOC entry 5211 (class 0 OID 17285)
-- Dependencies: 233
-- Data for Name: service; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.service VALUES (4016, 2035, 3016, 8016, 6003, '2025-10-09', '2025-10-29', 'HP sering restart', 400000.00, 'Service selesai, dilakukan penggantian sparepart tambahan', 'Tambahan sparepart kipas pendingin', 'Firmware Update', 5016, 1027);
INSERT INTO public.service VALUES (4011, 2025, 3011, 8011, 6006, '2025-09-29', '2025-12-02', 'Kulkas tidak dingin', 500000.00, 'Isi ulang freon', 'Kompresor normal', 'Freon R134a', 5011, 1021);
INSERT INTO public.service VALUES (4012, 2026, 3012, 8012, 6004, '2025-09-30', '2025-10-09', 'AC bocor air', 300000.00, 'Pipa diganti dan dibersihkan', 'Tes suhu 18C stabil', 'Pipa AC 1PK', 5012, 1022);
INSERT INTO public.service VALUES (4013, 2028, 3013, 8013, 6003, '2025-10-01', '2025-10-11', 'TV suara hilang', 230000.00, 'Ganti speaker internal', 'Tes suara full volume OK', 'Speaker 10W', 5013, 1023);
INSERT INTO public.service VALUES (4014, 2030, 3014, 8014, 6002, '2025-10-03', '2025-10-11', 'Printer tidak mau scan', 160000.00, 'Driver dan sensor diganti', 'Tes scan sukses', 'Sensor Scanner', 5014, 1024);
INSERT INTO public.service VALUES (4015, 2032, 3015, 8015, 6001, '2025-10-04', '2025-10-14', 'Laptop tidak nyala', 400000.00, 'Ganti motherboard', 'Tes hidup normal', 'Motherboard Lenovo', 5015, 1025);
INSERT INTO public.service VALUES (4017, 2038, 3017, 8017, 6004, '2025-10-11', '2025-10-19', 'Kamera error lensa', 370000.00, 'Ganti modul lensa', 'Tes fokus otomatis OK', 'Modul Lensa Canon', 5017, 1037);
INSERT INTO public.service VALUES (4018, 2040, 3018, 8018, 6005, '2025-10-13', '2025-10-20', 'Smartphone ghost touch', 280000.00, 'Ganti touchscreen', 'Tes sentuhan lancar', 'Touchscreen Xiaomi', 5018, 1039);
INSERT INTO public.service VALUES (4019, 2041, 3019, 8019, 6002, '2025-10-15', '2025-10-21', 'Laptop mati setelah update', 300000.00, 'Install ulang OS', 'Tes software stabil', 'SSD 512GB', 5019, 1040);
INSERT INTO public.service VALUES (4020, 2042, 3020, 8020, 6001, '2025-10-19', '2025-10-26', 'Kulkas bocor freon', 420000.00, 'Isi ulang freon dan tambal pipa', 'Tes suhu 5C stabil', 'Freon R22', 5020, 1040);
INSERT INTO public.service VALUES (4071, 2021, 3001, 8001, 6002, '2025-10-24', NULL, 'Laptop tidak bisa connect WiFi', 150000.00, 'Ganti modul WiFi', 'Masih proses', 'true', 5016, 1018);
INSERT INTO public.service VALUES (4072, 2022, 3002, 8002, 6003, '2025-10-25', '2025-10-28', 'HP mati total', 250000.00, 'Ganti IC Power', 'Normal', 'true', 5017, 1019);
INSERT INTO public.service VALUES (4073, 2023, 3003, 8003, 6003, '2025-10-24', '2025-10-27', 'Laptop tidak bisa booting', 300000.00, 'Ganti SSD', 'Selesai', 'true', 5018, 1020);
INSERT INTO public.service VALUES (4074, 2024, 3004, 8004, 6002, '2025-10-25', NULL, 'TV tidak keluar suara', 250000.00, 'IC audio rusak', 'Menunggu part', 'true', 5019, 1021);
INSERT INTO public.service VALUES (4075, 2025, 3005, 8005, 6003, '2025-10-23', '2025-10-26', 'Smartwatch tidak nyala', 180000.00, 'Ganti baterai', 'Berfungsi', 'true', 5020, 1022);
INSERT INTO public.service VALUES (4076, 2026, 3006, 8006, 6003, '2025-10-21', '2025-10-25', 'Kipas tidak berputar', 100000.00, 'Bersihkan motor kipas', 'Oke', 'false', NULL, 1023);
INSERT INTO public.service VALUES (4077, 2027, 3007, 8007, 6003, '2025-10-22', '2025-10-26', 'Kamera blur', 220000.00, 'Kalibrasi lensa', 'Selesai', 'true', 5021, 1024);
INSERT INTO public.service VALUES (4078, 2028, 3008, 8008, 6003, '2025-10-20', '2025-10-24', 'Laptop restart sendiri', 230000.00, 'Cek RAM dan PSU', 'Stabil', 'true', 5022, 1025);
INSERT INTO public.service VALUES (4079, 2029, 3009, 8009, 6003, '2025-10-18', '2025-10-23', 'TV bergaris', 270000.00, 'Ganti kabel fleksibel', 'Oke', 'true', 5023, 1026);
INSERT INTO public.service VALUES (4080, 2030, 3010, 8010, 6002, '2025-10-26', NULL, 'Printer error paper jam', 150000.00, 'Masih proses', 'Menunggu sparepart', 'false', NULL, 1027);
INSERT INTO public.service VALUES (4081, 2031, 3011, 8011, 6003, '2025-10-22', '2025-10-27', 'Kulkas bocor', 300000.00, 'Las pipa bocor', 'Selesai', 'true', 5024, 1028);
INSERT INTO public.service VALUES (4082, 2032, 3012, 8012, 6003, '2025-10-21', '2025-10-24', 'Monitor berbayang', 270000.00, 'Ganti panel', 'Clear', 'true', 5025, 1029);
INSERT INTO public.service VALUES (4083, 2033, 3013, 8013, 6003, '2025-10-20', '2025-10-25', 'Speaker dengung', 120000.00, 'Ganti kapasitor', 'Fix', 'true', 5026, 1030);
INSERT INTO public.service VALUES (4084, 2034, 3014, 8014, 6003, '2025-10-19', '2025-10-24', 'Tablet stuck logo', 200000.00, 'Flash ulang', 'Normal', 'true', 5027, 1031);
INSERT INTO public.service VALUES (4085, 2035, 3015, 8015, 6003, '2025-10-23', '2025-10-26', 'Laptop keyboard error', 150000.00, 'Ganti keyboard', 'Oke', 'true', 5028, 1032);
INSERT INTO public.service VALUES (4086, 2036, 3016, 8016, 6003, '2025-10-24', '2025-10-27', 'Smartphone mic tidak berfungsi', 120000.00, 'Ganti mic', 'Oke', 'true', 5029, 1033);
INSERT INTO public.service VALUES (4087, 2037, 3017, 8017, 6003, '2025-10-25', '2025-10-28', 'Smart TV tidak connect WiFi', 150000.00, 'Ganti modul WiFi', 'Normal', 'true', 5030, 1034);
INSERT INTO public.service VALUES (4088, 2038, 3018, 8018, 6003, '2025-10-26', '2025-10-28', 'Kamera tidak bisa zoom', 250000.00, 'Ganti motor lensa', 'Oke', 'true', 5031, 1035);
INSERT INTO public.service VALUES (4089, 2039, 3019, 8019, 6003, '2025-10-27', '2025-10-28', 'AC bocor air', 220000.00, 'Bersihkan filter', 'Normal', 'false', NULL, 1036);
INSERT INTO public.service VALUES (4090, 2040, 3020, 8020, 6003, '2025-10-28', '2025-10-29', 'HP cepat panas', 150000.00, 'Ganti baterai', 'Sudah ok', 'true', 5032, 1037);
INSERT INTO public.service VALUES (4091, 2041, 3001, 8001, 6003, '2025-10-26', '2025-10-29', 'Konsol overheat', 280000.00, 'Ganti thermal pad', 'Selesai', 'true', 5033, 1038);
INSERT INTO public.service VALUES (4092, 2042, 3002, 8002, 6003, '2025-10-27', '2025-10-29', 'Laptop tidak bisa charge', 190000.00, 'Ganti charger IC', 'Normal', 'true', 5034, 1039);
INSERT INTO public.service VALUES (4093, 2043, 3003, 8003, 6003, '2025-10-24', '2025-10-28', 'TV mati setengah layar', 260000.00, 'Ganti panel', 'Fix', 'true', 5035, 1040);
INSERT INTO public.service VALUES (4094, 2044, 3004, 8004, 6003, '2025-10-26', '2025-10-29', 'Smartwatch tidak connect Bluetooth', 150000.00, 'Flash ulang firmware', 'Oke', 'true', 5036, 1031);
INSERT INTO public.service VALUES (4095, 2045, 3005, 8005, 6003, '2025-10-25', '2025-10-28', 'Rice cooker indikator mati', 120000.00, 'Ganti kabel power', 'Oke', 'true', 5037, 1032);
INSERT INTO public.service VALUES (4096, 2046, 3006, 8006, 6003, '2025-10-27', '2025-10-29', 'TV suara pecah', 130000.00, 'Perbaiki speaker', 'Selesai', 'true', 5038, 1033);
INSERT INTO public.service VALUES (4097, 2047, 3007, 8007, 6003, '2025-10-28', '2025-10-29', 'Kulkas terlalu dingin', 100000.00, 'Atur thermostat', 'Normal', 'false', NULL, 1034);
INSERT INTO public.service VALUES (4098, 2048, 3008, 8008, 6003, '2025-10-27', '2025-10-29', 'Laptop fan bising', 90000.00, 'Bersihkan fan', 'Oke', 'false', NULL, 1035);
INSERT INTO public.service VALUES (4099, 2049, 3009, 8009, 6003, '2025-10-26', '2025-10-28', 'HP sinyal hilang', 150000.00, 'Ganti antena', 'Berfungsi', 'true', 5039, 1036);
INSERT INTO public.service VALUES (4100, 2050, 3010, 8010, 6003, '2025-10-27', '2025-10-29', 'Kamera hasil gelap', 170000.00, 'Ganti sensor', 'Fix', 'true', 5040, 1037);
INSERT INTO public.service VALUES (4001, 2001, 3001, 8001, 6003, '2025-09-09', '2025-10-29', 'Layar retak setelah jatuh', 350000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Uji fungsi touchscreen OK', 'LCD Samsung A14', 5001, 1001);
INSERT INTO public.service VALUES (4002, 2002, 3002, 8002, 6003, '2025-09-11', '2025-10-29', 'Baterai cepat habis', 250000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Kapasitas full 4000mAh', 'Baterai HP Pavilion', 5002, 1001);
INSERT INTO public.service VALUES (4003, 2003, 3003, 8003, 6003, '2025-09-12', '2025-10-29', 'Laptop sering restart', 300000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Tes stabil 4 jam', 'RAM DDR4 8GB', 5003, 1002);
INSERT INTO public.service VALUES (4004, 2004, 3004, 8004, 6003, '2025-09-13', '2025-10-29', 'Printer tidak menarik kertas', 150000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Uji cetak sukses', 'Roller Canon', 5004, 1002);
INSERT INTO public.service VALUES (4005, 2005, 3005, 8005, 6003, '2025-09-14', '2025-10-29', 'Laptop overheat', 275000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Tes suhu stabil 60C', 'Thermal Paste Arctic', 5005, 1003);
INSERT INTO public.service VALUES (4006, 2010, 3006, 8006, 6003, '2025-09-19', '2025-10-29', 'Speaker mati total', 180000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Tes suara kiri-kanan OK', 'IC Audio', 5006, 1005);
INSERT INTO public.service VALUES (4007, 2012, 3007, 8007, 6003, '2025-09-21', '2025-10-29', 'Smartphone mati total', 320000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Arus 0.45A normal', 'IC Power', 5007, 1006);
INSERT INTO public.service VALUES (4008, 2014, 3008, 8008, 6003, '2025-09-23', '2025-10-29', 'TV tidak menampilkan gambar', 400000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Tes gambar full color OK', 'Panel TV LG', 5008, 1011);
INSERT INTO public.service VALUES (4009, 2017, 3009, 8009, 6003, '2025-09-25', '2025-10-29', 'Laptop lemot', 220000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Boot time 10 detik', 'SSD Kingston 256GB', 5009, 1013);
INSERT INTO public.service VALUES (4010, 2020, 3010, 8010, 6003, '2025-09-26', '2025-10-29', 'Tablet tidak bisa charge', 210000.00, 'Perangkat sudah diperbaiki dan siap diambil', 'Tes charge OK', 'Konektor Type-C', 5010, 1016);


--
-- TOC entry 5212 (class 0 OID 17320)
-- Dependencies: 234
-- Data for Name: sparepart; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.sparepart VALUES (5001, 'Baterai Samsung Galaxy A14', 25, 150000.00, 'Samsung', '2025-10-19', NULL);
INSERT INTO public.sparepart VALUES (5002, 'Layar LCD HP Pavilion 14', 10, 750000.00, 'HP', '2025-10-21', NULL);
INSERT INTO public.sparepart VALUES (5003, 'Keyboard Laptop Asus', 40, 200000.00, 'Asus', '2025-10-22', NULL);
INSERT INTO public.sparepart VALUES (5004, 'Adaptor Laptop Lenovo 65W', 35, 180000.00, 'Lenovo', '2025-10-17', NULL);
INSERT INTO public.sparepart VALUES (5005, 'Tinta Printer Canon G2010', 50, 120000.00, 'Canon', '2025-10-23', NULL);
INSERT INTO public.sparepart VALUES (5006, 'Speaker Polytron PAS 79', 15, 450000.00, 'Polytron', '2025-10-24', NULL);
INSERT INTO public.sparepart VALUES (5007, 'Fan Pendingin Laptop Acer', 20, 150000.00, 'Acer', '2025-10-20', NULL);
INSERT INTO public.sparepart VALUES (5008, 'Power Supply 450W', 30, 350000.00, 'Corsair', '2025-10-19', NULL);
INSERT INTO public.sparepart VALUES (5009, 'IC Audio Oppo A57', 60, 90000.00, 'Oppo', '2025-10-25', NULL);
INSERT INTO public.sparepart VALUES (5010, 'Panel LED LG 43"', 8, 950000.00, 'LG', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5011, 'Motherboard Asus ROG', 12, 2200000.00, 'Asus', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5012, 'RAM DDR4 8GB Kingston', 45, 400000.00, 'Kingston', '2025-10-23', NULL);
INSERT INTO public.sparepart VALUES (5013, 'SSD 512GB Samsung', 25, 900000.00, 'Samsung', '2025-10-18', NULL);
INSERT INTO public.sparepart VALUES (5014, 'Kipas Pendingin PC', 30, 80000.00, 'DeepCool', '2025-10-21', NULL);
INSERT INTO public.sparepart VALUES (5015, 'Kabel Power Printer Canon', 70, 50000.00, 'Canon', '2025-10-22', NULL);
INSERT INTO public.sparepart VALUES (5016, 'Charger Laptop HP 45W', 18, 190000.00, 'HP', '2025-10-19', NULL);
INSERT INTO public.sparepart VALUES (5017, 'Sensor Kamera Sony A6000', 6, 1200000.00, 'Sony', '2025-10-20', NULL);
INSERT INTO public.sparepart VALUES (5018, 'Kipas Prosesor Intel', 20, 130000.00, 'Intel', '2025-10-25', NULL);
INSERT INTO public.sparepart VALUES (5019, 'Motherboard MSI B450', 10, 1800000.00, 'MSI', '2025-10-24', NULL);
INSERT INTO public.sparepart VALUES (5020, 'Panel Sentuh Xiaomi 11T', 22, 700000.00, 'Xiaomi', '2025-10-23', NULL);
INSERT INTO public.sparepart VALUES (5021, 'Kipas Dinding 16 Inch', 25, 250000.00, 'Miyako', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5022, 'Pintu Kulkas Sharp', 12, 850000.00, 'Sharp', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5023, 'Filter AC Daikin', 40, 90000.00, 'Daikin', '2025-10-28', NULL);
INSERT INTO public.sparepart VALUES (5024, 'Kompresor AC LG', 5, 1700000.00, 'LG', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5025, 'Motor Mesin Cuci Samsung', 9, 800000.00, 'Samsung', '2025-10-25', NULL);
INSERT INTO public.sparepart VALUES (5026, 'Panel Tombol TV Polytron', 18, 300000.00, 'Polytron', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5027, 'Kabel Power Supply Laptop', 50, 70000.00, 'Universal', '2025-10-24', NULL);
INSERT INTO public.sparepart VALUES (5028, 'RAM DDR5 16GB Crucial', 15, 1100000.00, 'Crucial', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5029, 'SSD NVMe 1TB WD', 10, 1350000.00, 'Western Digital', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5030, 'Sensor Proximity Oppo Reno', 25, 120000.00, 'Oppo', '2025-10-28', NULL);
INSERT INTO public.sparepart VALUES (5031, 'Motherboard Laptop Dell', 8, 2100000.00, 'Dell', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5032, 'Tombol Power Laptop Acer', 35, 60000.00, 'Acer', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5033, 'Speaker Laptop Lenovo', 25, 90000.00, 'Lenovo', '2025-10-24', NULL);
INSERT INTO public.sparepart VALUES (5034, 'Touchpad Asus VivoBook', 18, 170000.00, 'Asus', '2025-10-22', NULL);
INSERT INTO public.sparepart VALUES (5035, 'Engsel Laptop HP', 22, 140000.00, 'HP', '2025-10-23', NULL);
INSERT INTO public.sparepart VALUES (5036, 'Baterai Laptop Dell 56Wh', 15, 450000.00, 'Dell', '2025-10-26', NULL);
INSERT INTO public.sparepart VALUES (5037, 'Kipas Laptop Toshiba', 10, 130000.00, 'Toshiba', '2025-10-27', NULL);
INSERT INTO public.sparepart VALUES (5038, 'Kabel HDMI 1.5m', 80, 50000.00, 'Vention', '2025-10-28', NULL);
INSERT INTO public.sparepart VALUES (5039, 'Adaptor Type-C to HDMI', 40, 120000.00, 'Ugreen', '2025-10-28', NULL);
INSERT INTO public.sparepart VALUES (5040, 'Casing Laptop Lenovo IdeaPad', 12, 300000.00, 'Lenovo', '2025-10-25', NULL);


--
-- TOC entry 5210 (class 0 OID 17273)
-- Dependencies: 232
-- Data for Name: status_perbaikan; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.status_perbaikan VALUES (6001, 'Diterima', 'Perangkat telah diterima oleh teknisi untuk pemeriksaan awal');
INSERT INTO public.status_perbaikan VALUES (6002, 'Diagnosa', 'Teknisi sedang melakukan pemeriksaan dan diagnosa kerusakan');
INSERT INTO public.status_perbaikan VALUES (6003, 'Menunggu Konfirmasi', 'Menunggu konfirmasi pelanggan terkait biaya dan waktu perbaikan');
INSERT INTO public.status_perbaikan VALUES (6004, 'Dalam Perbaikan', 'Proses perbaikan sedang dilakukan oleh teknisi');
INSERT INTO public.status_perbaikan VALUES (6005, 'Menunggu Sparepart', 'Perbaikan tertunda karena menunggu kedatangan sparepart');
INSERT INTO public.status_perbaikan VALUES (6006, 'Selesai Diperbaiki', 'Perangkat sudah selesai diperbaiki dan siap diuji');
INSERT INTO public.status_perbaikan VALUES (6007, 'Uji Coba', 'Perangkat sedang diuji untuk memastikan hasil perbaikan');
INSERT INTO public.status_perbaikan VALUES (6008, 'Siap Diambil', 'Perangkat sudah siap diambil oleh pelanggan');
INSERT INTO public.status_perbaikan VALUES (6009, 'Diambil Pelanggan', 'Perangkat telah diambil oleh pelanggan');
INSERT INTO public.status_perbaikan VALUES (6010, 'Dibatalkan', 'Perbaikan dibatalkan oleh pelanggan atau admin');


--
-- TOC entry 5208 (class 0 OID 17244)
-- Dependencies: 230
-- Data for Name: teknisi; Type: TABLE DATA; Schema: public; Owner: postgres
--

INSERT INTO public.teknisi VALUES (3001, 'Andi Pratama', 'Smartphone & Tablet', '081234567801', 'andipratama@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3002, 'Budi Santoso', 'Laptop & Komputer', '081234567802', 'budisantoso@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3003, 'Citra Lestari', 'Printer & Scanner', '081234567803', 'citralestari@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3004, 'Dedi Rahman', 'Elektronik Rumah', '081234567804', 'dedirahman@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3005, 'Eka Putri', 'Smartphone', '081234567805', 'ekaputri@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3006, 'Fajar Nugraha', 'Laptop & Komputer', '081234567806', 'fajarnugraha@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3007, 'Gilang Saputra', 'AC & Kulkas', '081234567807', 'gilangsaputra@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3008, 'Hani Marlina', 'Smartphone & Tablet', '081234567808', 'hanimarlina@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3009, 'Irfan Kurniawan', 'Printer & Scanner', '081234567809', 'irfankurniawan@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3010, 'Joko Widodo', 'Laptop & Komputer', '081234567810', 'jokowidodo@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3011, 'Kiki Anggraini', 'Elektronik Rumah', '081234567811', 'kikianggraini@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3012, 'Lutfi Hidayat', 'Smartphone & Tablet', '081234567812', 'lutfihidayat@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3013, 'Maya Sari', 'Printer & Scanner', '081234567813', 'maya@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3014, 'Nanda Prakoso', 'Laptop & Komputer', '081234567814', 'nandaprakoso@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3015, 'Oka Wijaya', 'AC & Kulkas', '081234567815', 'okawijaya@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3016, 'Putri Ayuningtyas', 'Smartphone & Tablet', '081234567816', 'putriayuningtyas@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3017, 'Rian Firmansyah', 'Elektronik Rumah', '081234567817', 'rianfirmansyah@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3018, 'Sari Indah', 'Printer & Scanner', '081234567818', 'sariindah@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3019, 'Taufik Hidayat', 'Laptop & Komputer', '081234567819', 'taufikhidayat@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');
INSERT INTO public.teknisi VALUES (3020, 'Wulan Dewi', 'Smartphone & Tablet', '081234567820', 'wulandewi@gmail.com', true, '$2y$10$Og9q4Yk9972z9cYd2d3kqOYQhZzL8d0kYwv5x6Y2n8JlF3nZP4i9C');


--
-- TOC entry 5223 (class 0 OID 0)
-- Dependencies: 227
-- Name: seq_admin; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_admin', 8020, true);


--
-- TOC entry 5224 (class 0 OID 0)
-- Dependencies: 220
-- Name: seq_pelanggan; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_pelanggan', 1040, true);


--
-- TOC entry 5225 (class 0 OID 0)
-- Dependencies: 226
-- Name: seq_pembayaran; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_pembayaran', 7154, true);


--
-- TOC entry 5226 (class 0 OID 0)
-- Dependencies: 221
-- Name: seq_perangkat; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_perangkat', 2055, true);


--
-- TOC entry 5227 (class 0 OID 0)
-- Dependencies: 223
-- Name: seq_service; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_service', 4101, true);


--
-- TOC entry 5228 (class 0 OID 0)
-- Dependencies: 224
-- Name: seq_sparepart; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_sparepart', 5040, true);


--
-- TOC entry 5229 (class 0 OID 0)
-- Dependencies: 225
-- Name: seq_status_perbaikan; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_status_perbaikan', 6010, true);


--
-- TOC entry 5230 (class 0 OID 0)
-- Dependencies: 222
-- Name: seq_teknisi; Type: SEQUENCE SET; Schema: public; Owner: postgres
--

SELECT pg_catalog.setval('public.seq_teknisi', 3020, true);


--
-- TOC entry 5012 (class 2606 OID 17272)
-- Name: admin admin_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_email_key UNIQUE (email);


--
-- TOC entry 5014 (class 2606 OID 17268)
-- Name: admin admin_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_pkey PRIMARY KEY (id_admin);


--
-- TOC entry 5016 (class 2606 OID 17270)
-- Name: admin admin_username_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.admin
    ADD CONSTRAINT admin_username_key UNIQUE (username);


--
-- TOC entry 4993 (class 2606 OID 17226)
-- Name: pelanggan pelanggan_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_email_key UNIQUE (email);


--
-- TOC entry 4995 (class 2606 OID 17224)
-- Name: pelanggan pelanggan_no_hp_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_no_hp_key UNIQUE (no_hp);


--
-- TOC entry 4997 (class 2606 OID 17222)
-- Name: pelanggan pelanggan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pelanggan
    ADD CONSTRAINT pelanggan_pkey PRIMARY KEY (id_pelanggan);


--
-- TOC entry 5033 (class 2606 OID 17345)
-- Name: pembayaran pembayaran_id_service_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pembayaran
    ADD CONSTRAINT pembayaran_id_service_key UNIQUE (id_service);


--
-- TOC entry 5035 (class 2606 OID 17343)
-- Name: pembayaran pembayaran_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pembayaran
    ADD CONSTRAINT pembayaran_pkey PRIMARY KEY (id_pembayaran);


--
-- TOC entry 5000 (class 2606 OID 17238)
-- Name: perangkat perangkat_nomor_seri_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perangkat
    ADD CONSTRAINT perangkat_nomor_seri_key UNIQUE (nomor_seri);


--
-- TOC entry 5002 (class 2606 OID 17236)
-- Name: perangkat perangkat_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perangkat
    ADD CONSTRAINT perangkat_pkey PRIMARY KEY (id_perangkat);


--
-- TOC entry 5026 (class 2606 OID 17299)
-- Name: service service_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT service_pkey PRIMARY KEY (id_service);


--
-- TOC entry 5029 (class 2606 OID 17330)
-- Name: sparepart sparepart_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sparepart
    ADD CONSTRAINT sparepart_pkey PRIMARY KEY (id_sparepart);


--
-- TOC entry 5018 (class 2606 OID 17284)
-- Name: status_perbaikan status_perbaikan_nama_status_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.status_perbaikan
    ADD CONSTRAINT status_perbaikan_nama_status_key UNIQUE (nama_status);


--
-- TOC entry 5020 (class 2606 OID 17282)
-- Name: status_perbaikan status_perbaikan_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.status_perbaikan
    ADD CONSTRAINT status_perbaikan_pkey PRIMARY KEY (id_status);


--
-- TOC entry 5006 (class 2606 OID 17256)
-- Name: teknisi teknisi_email_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teknisi
    ADD CONSTRAINT teknisi_email_key UNIQUE (email);


--
-- TOC entry 5008 (class 2606 OID 17254)
-- Name: teknisi teknisi_no_hp_key; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teknisi
    ADD CONSTRAINT teknisi_no_hp_key UNIQUE (no_hp);


--
-- TOC entry 5010 (class 2606 OID 17252)
-- Name: teknisi teknisi_pkey; Type: CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.teknisi
    ADD CONSTRAINT teknisi_pkey PRIMARY KEY (id_teknisi);


--
-- TOC entry 4991 (class 1259 OID 17655)
-- Name: idx_pelanggan_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_pelanggan_nama ON public.pelanggan USING btree (lower((nama)::text));


--
-- TOC entry 5030 (class 1259 OID 17653)
-- Name: idx_pembayaran_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_pembayaran_status ON public.pembayaran USING btree (status_bayar);


--
-- TOC entry 5031 (class 1259 OID 17358)
-- Name: idx_pembayaran_tanggal; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_pembayaran_tanggal ON public.pembayaran USING btree (tanggal_bayar);


--
-- TOC entry 4998 (class 1259 OID 17353)
-- Name: idx_perangkat_merek; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_perangkat_merek ON public.perangkat USING btree (lower((merek)::text));


--
-- TOC entry 5021 (class 1259 OID 17356)
-- Name: idx_service_belum_selesai; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_service_belum_selesai ON public.service USING btree (id_status) WHERE (tanggal_selesai IS NULL);


--
-- TOC entry 5022 (class 1259 OID 17652)
-- Name: idx_service_id; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_service_id ON public.service USING btree (id_service);


--
-- TOC entry 5023 (class 1259 OID 17609)
-- Name: idx_service_teknisi; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_service_teknisi ON public.service USING btree (id_teknisi);


--
-- TOC entry 5024 (class 1259 OID 17355)
-- Name: idx_service_teknisi_status; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_service_teknisi_status ON public.service USING btree (id_teknisi, id_status);


--
-- TOC entry 5027 (class 1259 OID 17357)
-- Name: idx_sparepart_stok_rendah; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_sparepart_stok_rendah ON public.sparepart USING btree (stok) WHERE (stok < 5);


--
-- TOC entry 5003 (class 1259 OID 17354)
-- Name: idx_teknisi_nama; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_teknisi_nama ON public.teknisi USING btree (lower((nama_teknisi)::text));


--
-- TOC entry 5004 (class 1259 OID 17610)
-- Name: idx_teknisi_trgm; Type: INDEX; Schema: public; Owner: postgres
--

CREATE INDEX idx_teknisi_trgm ON public.teknisi USING gin (lower((nama_teknisi)::text) public.gin_trgm_ops);


--
-- TOC entry 5044 (class 2620 OID 17657)
-- Name: service tr_update_status_selesai; Type: TRIGGER; Schema: public; Owner: postgres
--

CREATE TRIGGER tr_update_status_selesai BEFORE UPDATE ON public.service FOR EACH ROW EXECUTE FUNCTION public.fn_update_status_selesai();


--
-- TOC entry 5043 (class 2606 OID 17346)
-- Name: pembayaran fk_pembayaran_service; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.pembayaran
    ADD CONSTRAINT fk_pembayaran_service FOREIGN KEY (id_service) REFERENCES public.service(id_service) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5036 (class 2606 OID 17239)
-- Name: perangkat fk_perangkat_pelanggan; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.perangkat
    ADD CONSTRAINT fk_perangkat_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES public.pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5037 (class 2606 OID 17310)
-- Name: service fk_service_admin; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_service_admin FOREIGN KEY (id_admin) REFERENCES public.admin(id_admin) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5038 (class 2606 OID 17424)
-- Name: service fk_service_pelanggan; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_service_pelanggan FOREIGN KEY (id_pelanggan) REFERENCES public.pelanggan(id_pelanggan) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5039 (class 2606 OID 17300)
-- Name: service fk_service_perangkat; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_service_perangkat FOREIGN KEY (id_perangkat) REFERENCES public.perangkat(id_perangkat) ON UPDATE CASCADE ON DELETE CASCADE;


--
-- TOC entry 5040 (class 2606 OID 17315)
-- Name: service fk_service_status; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_service_status FOREIGN KEY (id_status) REFERENCES public.status_perbaikan(id_status) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5041 (class 2606 OID 17305)
-- Name: service fk_service_teknisi; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.service
    ADD CONSTRAINT fk_service_teknisi FOREIGN KEY (id_teknisi) REFERENCES public.teknisi(id_teknisi) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5042 (class 2606 OID 17419)
-- Name: sparepart fk_sparepart_service; Type: FK CONSTRAINT; Schema: public; Owner: postgres
--

ALTER TABLE ONLY public.sparepart
    ADD CONSTRAINT fk_sparepart_service FOREIGN KEY (id_service) REFERENCES public.service(id_service) ON UPDATE CASCADE ON DELETE SET NULL;


--
-- TOC entry 5214 (class 0 OID 17593)
-- Dependencies: 236 5217
-- Name: mv_kinerja_teknisi; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.mv_kinerja_teknisi;


--
-- TOC entry 5215 (class 0 OID 17631)
-- Dependencies: 241 5217
-- Name: mv_pendapatan_bulanan; Type: MATERIALIZED VIEW DATA; Schema: public; Owner: postgres
--

REFRESH MATERIALIZED VIEW public.mv_pendapatan_bulanan;


-- Completed on 2025-12-08 22:10:34

--
-- PostgreSQL database dump complete
--

\unrestrict W9NcCyGPcDFSbbH8zcXyRoY1GlBzjVsUle5vRfyaGiuDaN6BnWTPd89lffLEIgo

