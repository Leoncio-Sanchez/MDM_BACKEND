-- Esquema de producción del sistema LF-ITSE (tablas que usa el backend Laravel).
-- Origen: dump 2026.sql (pg_dump de 'myapp'), validado columna por columna contra el
-- esquema de producción (D:/MDM/BD/data-production). Se omiten las tablas internas de
-- Django (django_*, auth_group*, token_blacklist_*) y las columnas que solo existían en el dump.

-- Tablas (25)
CREATE TABLE public.auth_user (
    id integer NOT NULL,
    password character varying(128) NOT NULL,
    last_login timestamp with time zone,
    is_superuser boolean NOT NULL,
    username character varying(150) NOT NULL,
    first_name character varying(150) NOT NULL,
    last_name character varying(150) NOT NULL,
    email character varying(254) NOT NULL,
    is_staff boolean NOT NULL,
    is_active boolean NOT NULL,
    date_joined timestamp with time zone NOT NULL
);
CREATE TABLE public.autorizaciones_improcedentes (
    id bigint NOT NULL,
    tipo_autorizacion character varying(4) NOT NULL,
    fecha_rechazo date NOT NULL,
    documento character varying(100) NOT NULL,
    observaciones character varying(1000) NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    usuario_id integer NOT NULL,
    expediente_id bigint NOT NULL
);
CREATE TABLE public.estados (
    id bigint NOT NULL,
    nombre character varying(100) NOT NULL,
    es_para_lf boolean NOT NULL,
    es_para_itse boolean NOT NULL,
    esta_activo boolean NOT NULL
);
CREATE TABLE public.expedientes (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    numero_expediente integer NOT NULL,
    fecha_recepcion timestamp with time zone NOT NULL,
    observaciones character varying(250),
    fecha_vencimiento date NOT NULL,
    fecha_alerta date NOT NULL,
    fecha_suspension date,
    dias_ampliacion integer,
    motivo_ampliacion character varying(250),
    fecha_digitacion_ampliacion timestamp with time zone,
    fecha_digitacion timestamp with time zone NOT NULL,
    usuario_id integer NOT NULL,
    usuario_ampliacion integer,
    representante_id bigint,
    solicitante_id bigint NOT NULL,
    tipo_procedimiento_tupa_id bigint NOT NULL,
    CONSTRAINT expedientes_dias_ampliacion_check CHECK ((dias_ampliacion >= 0)),
    CONSTRAINT expedientes_numero_expediente_check CHECK ((numero_expediente >= 0))
);
CREATE TABLE public.expedientes_archivos (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    nombre_original character varying(255) NOT NULL,
    nombre_archivo character varying(255) NOT NULL,
    ruta_archivo character varying(500) NOT NULL,
    extension character varying(20) NOT NULL,
    tamanio_bytes bigint NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    expediente_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.feriados_anuales (
    id bigint NOT NULL,
    feriado date NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    user_id integer NOT NULL
);
CREATE TABLE public.feriados_recurrentes (
    id bigint NOT NULL,
    dia smallint NOT NULL,
    mes smallint NOT NULL,
    CONSTRAINT feriados_recurrentes_dia_check CHECK ((dia >= 0)),
    CONSTRAINT feriados_recurrentes_mes_check CHECK ((mes >= 0))
);
CREATE TABLE public.giros (
    id bigint NOT NULL,
    ciiu_id integer,
    nombre character varying(200) NOT NULL,
    esta_activo boolean NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    usuario_id integer NOT NULL,
    CONSTRAINT giros_ciiu_id_check CHECK ((ciiu_id >= 0))
);
CREATE TABLE public.itse (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    tipo_itse_id integer NOT NULL,
    numero_itse integer NOT NULL,
    fecha_expedicion date NOT NULL,
    fecha_solicitud_renovacion date NOT NULL,
    fecha_caducidad date NOT NULL,
    nombre_comercial character varying(250) NOT NULL,
    direccion character varying(250) NOT NULL,
    resolucion_numero character varying(50) NOT NULL,
    area numeric(18,2) NOT NULL,
    numero_recibo_pago character varying(20) NOT NULL,
    observaciones text NOT NULL,
    se_puede_publicar boolean NOT NULL,
    capacidad_aforo integer NOT NULL,
    fecha_notificacion timestamp with time zone,
    fecha_digitacion timestamp with time zone NOT NULL,
    expediente_id bigint NOT NULL,
    itse_principal_id bigint,
    usuario_id integer NOT NULL,
    nivel_riesgo_id bigint NOT NULL,
    conductor_id bigint NOT NULL,
    titular_id bigint NOT NULL,
    CONSTRAINT itse_capacidad_aforo_check CHECK ((capacidad_aforo >= 0)),
    CONSTRAINT itse_numero_itse_check CHECK ((numero_itse >= 0)),
    CONSTRAINT itse_tipo_itse_id_check CHECK ((tipo_itse_id >= 0))
);
CREATE TABLE public.itse_archivos (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    nombre_original character varying(255) NOT NULL,
    nombre_archivo character varying(255) NOT NULL,
    ruta_archivo character varying(500) NOT NULL,
    extension character varying(20) NOT NULL,
    tamanio_bytes bigint NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    itse_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.itse_estados (
    id bigint NOT NULL,
    fecha_estado date NOT NULL,
    documento character varying(100) NOT NULL,
    observaciones character varying(1000) NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    estado_id bigint NOT NULL,
    itse_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.itse_giros (
    id bigint NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    giro_id bigint NOT NULL,
    itse_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.licencias_funcionamiento (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    numero_licencia integer NOT NULL,
    fecha_emision date NOT NULL,
    nombre_comercial character varying(250) NOT NULL,
    es_vigencia_indeterminada boolean NOT NULL,
    fecha_inicio_vigencia date,
    fecha_fin_vigencia date,
    actividad character varying(50) NOT NULL,
    direccion character varying(250) NOT NULL,
    hora_desde integer NOT NULL,
    hora_hasta integer NOT NULL,
    resolucion_numero character varying(50) NOT NULL,
    area numeric(18,2) NOT NULL,
    numero_recibo_pago character varying(20) NOT NULL,
    observaciones text,
    se_puede_publicar boolean NOT NULL,
    fecha_notificacion timestamp with time zone,
    fecha_digitacion timestamp with time zone NOT NULL,
    expediente_id bigint NOT NULL,
    licencia_principal_id bigint,
    usuario_id integer NOT NULL,
    nivel_riesgo_id bigint NOT NULL,
    conductor_id bigint NOT NULL,
    titular_id bigint NOT NULL,
    tipo_licencia_id bigint NOT NULL,
    zonificacion_id bigint NOT NULL,
    CONSTRAINT licencias_funcionamiento_numero_licencia_check CHECK ((numero_licencia >= 0))
);
CREATE TABLE public.licencias_funcionamiento_archivos (
    id bigint NOT NULL,
    uuid uuid NOT NULL,
    nombre_original character varying(255) NOT NULL,
    nombre_archivo character varying(255) NOT NULL,
    ruta_archivo character varying(500) NOT NULL,
    extension character varying(20) NOT NULL,
    tamanio_bytes bigint NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    licencia_funcionamiento_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.licencias_funcionamiento_estados (
    id bigint NOT NULL,
    fecha_estado date NOT NULL,
    documento character varying(100) NOT NULL,
    observaciones character varying(1000) NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    estado_id bigint NOT NULL,
    licencia_funcionamiento_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.licencias_funcionamiento_giros (
    id bigint NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    giro_id bigint NOT NULL,
    licencia_funcionamiento_id bigint NOT NULL,
    usuario_id integer NOT NULL
);
CREATE TABLE public.niveles_riesgo (
    id bigint NOT NULL,
    codigo character varying(10) NOT NULL,
    nombre character varying(50) NOT NULL,
    esta_activo boolean NOT NULL
);
CREATE TABLE public.personas (
    id bigint NOT NULL,
    tipo_persona character varying(1) NOT NULL,
    sexo character varying(1) NOT NULL,
    apellido_paterno character varying(50),
    apellido_materno character varying(50),
    nombres character varying(100) NOT NULL,
    direccion character varying(250) NOT NULL,
    distrito character varying(100) NOT NULL,
    provincia character varying(100) NOT NULL,
    departamento character varying(100) NOT NULL,
    telefono character varying(30),
    correo_electronico character varying(150),
    fecha_creacion timestamp with time zone NOT NULL,
    fecha_actualizacion timestamp with time zone NOT NULL,
    user_id integer NOT NULL
);
CREATE TABLE public.personas_documentos (
    id bigint NOT NULL,
    numero_documento character varying(20) NOT NULL,
    persona_id bigint NOT NULL,
    tipo_documento_identidad_id bigint NOT NULL
);
CREATE TABLE public.tipos_documento_identidad (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(100) NOT NULL,
    esta_activo boolean NOT NULL,
    es_para_natural boolean NOT NULL,
    es_para_juridica boolean NOT NULL
);
CREATE TABLE public.tipos_licencia (
    id bigint NOT NULL,
    codigo character varying(20) NOT NULL,
    nombre character varying(150) NOT NULL,
    esta_activo boolean NOT NULL
);
CREATE TABLE public.tipos_procedimiento_tupa (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(250) NOT NULL,
    plazo_atencion_dias integer NOT NULL,
    dias_alerta_vencimiento integer NOT NULL,
    esta_activo boolean NOT NULL,
    requiere_lf boolean NOT NULL,
    requiere_itse boolean NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    usuario_id integer NOT NULL,
    unidad_organica_id bigint NOT NULL,
    CONSTRAINT tipos_procedimiento_tupa_dias_alerta_vencimiento_check CHECK ((dias_alerta_vencimiento >= 0)),
    CONSTRAINT tipos_procedimiento_tupa_plazo_atencion_dias_check CHECK ((plazo_atencion_dias >= 0))
);
CREATE TABLE public.unidades_organicas (
    id bigint NOT NULL,
    codigo character varying(50) NOT NULL,
    nombre character varying(200) NOT NULL,
    sigla character varying(50) NOT NULL,
    esta_activo boolean NOT NULL
);
CREATE TABLE public.usuarios_perfiles (
    id bigint NOT NULL,
    expedientes boolean NOT NULL,
    licencias boolean NOT NULL,
    itse boolean NOT NULL,
    admin boolean NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    user_id integer NOT NULL,
    user_id_digitador integer NOT NULL
);
CREATE TABLE public.zonificaciones (
    id bigint NOT NULL,
    codigo character varying(30) NOT NULL,
    nombre character varying(150) NOT NULL,
    esta_activo boolean NOT NULL,
    fecha_digitacion timestamp with time zone NOT NULL,
    usuario_id integer NOT NULL
);

-- Identidades (autoincrementales) (25)
ALTER TABLE public.auth_user ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.auth_user_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.autorizaciones_improcedentes ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.autorizaciones_improcedentes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.estados ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.estados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.expedientes_archivos ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.expedientes_archivos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.expedientes ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.expedientes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.feriados_anuales ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.feriados_anuales_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.feriados_recurrentes ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.feriados_recurrentes_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.giros ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.giros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.itse_archivos ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.itse_archivos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.itse_estados ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.itse_estados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.itse_giros ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.itse_giros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.itse ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.itse_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.licencias_funcionamiento_archivos ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.licencias_funcionamiento_archivos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.licencias_funcionamiento_estados ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.licencias_funcionamiento_estados_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.licencias_funcionamiento_giros ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.licencias_funcionamiento_giros_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.licencias_funcionamiento ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.licencias_funcionamiento_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.niveles_riesgo ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.niveles_riesgo_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.personas_documentos ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.personas_documentos_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.personas ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.personas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.tipos_documento_identidad ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.tipos_documento_identidad_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.tipos_licencia ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.tipos_licencia_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.tipos_procedimiento_tupa ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.tipos_procedimiento_tupa_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.unidades_organicas ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.unidades_organicas_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.usuarios_perfiles ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.usuarios_perfiles_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);
ALTER TABLE public.zonificaciones ALTER COLUMN id ADD GENERATED BY DEFAULT AS IDENTITY (
    SEQUENCE NAME public.zonificaciones_id_seq
    START WITH 1
    INCREMENT BY 1
    NO MINVALUE
    NO MAXVALUE
    CACHE 1
);

-- Claves primarias, únicas y CHECK (44)
ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.auth_user
    ADD CONSTRAINT auth_user_username_key UNIQUE (username);
ALTER TABLE ONLY public.autorizaciones_improcedentes
    ADD CONSTRAINT autorizaciones_improcedentes_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.estados
    ADD CONSTRAINT estados_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.expedientes_archivos
    ADD CONSTRAINT expedientes_archivos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.expedientes_archivos
    ADD CONSTRAINT expedientes_archivos_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.feriados_anuales
    ADD CONSTRAINT feriados_anuales_feriado_key UNIQUE (feriado);
ALTER TABLE ONLY public.feriados_anuales
    ADD CONSTRAINT feriados_anuales_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.feriados_recurrentes
    ADD CONSTRAINT feriados_recurrentes_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.giros
    ADD CONSTRAINT giros_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.itse_archivos
    ADD CONSTRAINT itse_archivos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.itse_archivos
    ADD CONSTRAINT itse_archivos_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.itse_estados
    ADD CONSTRAINT itse_estados_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.itse_giros
    ADD CONSTRAINT itse_giros_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_numero_itse_key UNIQUE (numero_itse);
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.licencias_funcionamiento_archivos
    ADD CONSTRAINT licencias_funcionamiento_archivos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.licencias_funcionamiento_archivos
    ADD CONSTRAINT licencias_funcionamiento_archivos_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.licencias_funcionamiento_estados
    ADD CONSTRAINT licencias_funcionamiento_estados_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.licencias_funcionamiento_giros
    ADD CONSTRAINT licencias_funcionamiento_giros_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_numero_licencia_key UNIQUE (numero_licencia);
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_uuid_key UNIQUE (uuid);
ALTER TABLE ONLY public.niveles_riesgo
    ADD CONSTRAINT niveles_riesgo_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.niveles_riesgo
    ADD CONSTRAINT niveles_riesgo_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.personas_documentos
    ADD CONSTRAINT personas_documentos_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.tipos_documento_identidad
    ADD CONSTRAINT tipos_documento_identidad_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.tipos_documento_identidad
    ADD CONSTRAINT tipos_documento_identidad_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.tipos_licencia
    ADD CONSTRAINT tipos_licencia_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.tipos_licencia
    ADD CONSTRAINT tipos_licencia_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.tipos_procedimiento_tupa
    ADD CONSTRAINT tipos_procedimiento_tupa_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.tipos_procedimiento_tupa
    ADD CONSTRAINT tipos_procedimiento_tupa_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.unidades_organicas
    ADD CONSTRAINT unidades_organicas_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.unidades_organicas
    ADD CONSTRAINT unidades_organicas_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.feriados_recurrentes
    ADD CONSTRAINT uq_feriados_rec_dia_mes UNIQUE (dia, mes);
ALTER TABLE ONLY public.personas_documentos
    ADD CONSTRAINT uq_perdoc_per_tip UNIQUE (persona_id, tipo_documento_identidad_id);
ALTER TABLE ONLY public.usuarios_perfiles
    ADD CONSTRAINT usuarios_perfiles_pkey PRIMARY KEY (id);
ALTER TABLE ONLY public.usuarios_perfiles
    ADD CONSTRAINT usuarios_perfiles_user_id_key UNIQUE (user_id);
ALTER TABLE ONLY public.zonificaciones
    ADD CONSTRAINT zonificaciones_codigo_key UNIQUE (codigo);
ALTER TABLE ONLY public.zonificaciones
    ADD CONSTRAINT zonificaciones_pkey PRIMARY KEY (id);

-- Índices (58)
CREATE INDEX auth_user_username_6821ab7c_like ON public.auth_user USING btree (username varchar_pattern_ops);
CREATE INDEX autorizaciones_improcedentes_expediente_id_b2ac250b ON public.autorizaciones_improcedentes USING btree (expediente_id);
CREATE INDEX autorizaciones_improcedentes_usuario_id_07a8e43a ON public.autorizaciones_improcedentes USING btree (usuario_id);
CREATE INDEX expedientes_archivos_expediente_id_22c81044 ON public.expedientes_archivos USING btree (expediente_id);
CREATE INDEX expedientes_archivos_usuario_id_302f312d ON public.expedientes_archivos USING btree (usuario_id);
CREATE INDEX expedientes_representante_id_b06a7961 ON public.expedientes USING btree (representante_id);
CREATE INDEX expedientes_solicitante_id_db9e101d ON public.expedientes USING btree (solicitante_id);
CREATE INDEX expedientes_tipo_procedimiento_tupa_id_b1a3d27e ON public.expedientes USING btree (tipo_procedimiento_tupa_id);
CREATE INDEX expedientes_usuario_ampliacion_9ca0df94 ON public.expedientes USING btree (usuario_ampliacion);
CREATE INDEX expedientes_usuario_id_67e66efd ON public.expedientes USING btree (usuario_id);
CREATE INDEX feriados_anuales_user_id_112e151c ON public.feriados_anuales USING btree (user_id);
CREATE INDEX giros_usuario_id_289a5cc6 ON public.giros USING btree (usuario_id);
CREATE INDEX itse_archivos_itse_id_3bd7d6a9 ON public.itse_archivos USING btree (itse_id);
CREATE INDEX itse_archivos_usuario_id_8ecb0313 ON public.itse_archivos USING btree (usuario_id);
CREATE INDEX itse_conductor_id_0c98cf06 ON public.itse USING btree (conductor_id);
CREATE INDEX itse_estados_estado_id_03f10c08 ON public.itse_estados USING btree (estado_id);
CREATE INDEX itse_estados_itse_id_219803f9 ON public.itse_estados USING btree (itse_id);
CREATE INDEX itse_estados_usuario_id_402903ca ON public.itse_estados USING btree (usuario_id);
CREATE INDEX itse_expediente_id_fb44ca9b ON public.itse USING btree (expediente_id);
CREATE INDEX itse_giros_giro_id_bd40279b ON public.itse_giros USING btree (giro_id);
CREATE INDEX itse_giros_itse_id_3fee7732 ON public.itse_giros USING btree (itse_id);
CREATE INDEX itse_giros_usuario_id_3dd6f70e ON public.itse_giros USING btree (usuario_id);
CREATE INDEX itse_itse_principal_id_6f07d7df ON public.itse USING btree (itse_principal_id);
CREATE INDEX itse_nivel_riesgo_id_cb56eaf6 ON public.itse USING btree (nivel_riesgo_id);
CREATE INDEX itse_titular_id_53721460 ON public.itse USING btree (titular_id);
CREATE INDEX itse_usuario_id_11e05815 ON public.itse USING btree (usuario_id);
CREATE INDEX ix_aut_impr_exp_tp ON public.autorizaciones_improcedentes USING btree (expediente_id, tipo_autorizacion);
CREATE INDEX ix_perdoc_tiponum ON public.personas_documentos USING btree (tipo_documento_identidad_id, numero_documento);
CREATE INDEX licencias_funcionamiento_a_licencia_funcionamiento_id_27cabff7 ON public.licencias_funcionamiento_archivos USING btree (licencia_funcionamiento_id);
CREATE INDEX licencias_funcionamiento_archivos_usuario_id_6d608437 ON public.licencias_funcionamiento_archivos USING btree (usuario_id);
CREATE INDEX licencias_funcionamiento_conductor_id_cf87f9e7 ON public.licencias_funcionamiento USING btree (conductor_id);
CREATE INDEX licencias_funcionamiento_e_licencia_funcionamiento_id_95591e9c ON public.licencias_funcionamiento_estados USING btree (licencia_funcionamiento_id);
CREATE INDEX licencias_funcionamiento_estados_estado_id_33006461 ON public.licencias_funcionamiento_estados USING btree (estado_id);
CREATE INDEX licencias_funcionamiento_estados_usuario_id_f5a551eb ON public.licencias_funcionamiento_estados USING btree (usuario_id);
CREATE INDEX licencias_funcionamiento_expediente_id_f855015f ON public.licencias_funcionamiento USING btree (expediente_id);
CREATE INDEX licencias_funcionamiento_g_licencia_funcionamiento_id_aa355771 ON public.licencias_funcionamiento_giros USING btree (licencia_funcionamiento_id);
CREATE INDEX licencias_funcionamiento_giros_giro_id_ea847b74 ON public.licencias_funcionamiento_giros USING btree (giro_id);
CREATE INDEX licencias_funcionamiento_giros_usuario_id_0247bb03 ON public.licencias_funcionamiento_giros USING btree (usuario_id);
CREATE INDEX licencias_funcionamiento_licencia_principal_id_bbaf125a ON public.licencias_funcionamiento USING btree (licencia_principal_id);
CREATE INDEX licencias_funcionamiento_nivel_riesgo_id_7d2ed6ac ON public.licencias_funcionamiento USING btree (nivel_riesgo_id);
CREATE INDEX licencias_funcionamiento_tipo_licencia_id_f14736d6 ON public.licencias_funcionamiento USING btree (tipo_licencia_id);
CREATE INDEX licencias_funcionamiento_titular_id_e760065f ON public.licencias_funcionamiento USING btree (titular_id);
CREATE INDEX licencias_funcionamiento_usuario_id_5c175952 ON public.licencias_funcionamiento USING btree (usuario_id);
CREATE INDEX licencias_funcionamiento_zonificacion_id_80234f39 ON public.licencias_funcionamiento USING btree (zonificacion_id);
CREATE INDEX niveles_riesgo_codigo_9986517c_like ON public.niveles_riesgo USING btree (codigo varchar_pattern_ops);
CREATE INDEX personas_documentos_persona_id_74a7c7f1 ON public.personas_documentos USING btree (persona_id);
CREATE INDEX personas_documentos_tipo_documento_identidad_id_01a14c84 ON public.personas_documentos USING btree (tipo_documento_identidad_id);
CREATE INDEX personas_user_id_475c5db3 ON public.personas USING btree (user_id);
CREATE INDEX tipos_documento_identidad_codigo_482699a3_like ON public.tipos_documento_identidad USING btree (codigo varchar_pattern_ops);
CREATE INDEX tipos_licencia_codigo_bd3032cb_like ON public.tipos_licencia USING btree (codigo varchar_pattern_ops);
CREATE INDEX tipos_procedimiento_tupa_codigo_2ae69ded_like ON public.tipos_procedimiento_tupa USING btree (codigo varchar_pattern_ops);
CREATE INDEX tipos_procedimiento_tupa_unidad_organica_id_e9dda056 ON public.tipos_procedimiento_tupa USING btree (unidad_organica_id);
CREATE INDEX tipos_procedimiento_tupa_usuario_id_02b698e5 ON public.tipos_procedimiento_tupa USING btree (usuario_id);
CREATE INDEX unidades_organicas_codigo_621a4390_like ON public.unidades_organicas USING btree (codigo varchar_pattern_ops);
CREATE UNIQUE INDEX uq_giros_ciiu_id ON public.giros USING btree (ciiu_id) WHERE (ciiu_id IS NOT NULL);
CREATE INDEX usuarios_perfiles_user_id_digitador_42b3baac ON public.usuarios_perfiles USING btree (user_id_digitador);
CREATE INDEX zonificaciones_codigo_e6a4585b_like ON public.zonificaciones USING btree (codigo varchar_pattern_ops);
CREATE INDEX zonificaciones_usuario_id_f1804683 ON public.zonificaciones USING btree (usuario_id);

-- Claves foráneas (49)
ALTER TABLE ONLY public.autorizaciones_improcedentes
    ADD CONSTRAINT autorizaciones_impro_expediente_id_b2ac250b_fk_expedient FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.autorizaciones_improcedentes
    ADD CONSTRAINT autorizaciones_impro_usuario_id_07a8e43a_fk_auth_user FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes_archivos
    ADD CONSTRAINT expedientes_archivos_expediente_id_22c81044_fk_expedientes_id FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes_archivos
    ADD CONSTRAINT expedientes_archivos_usuario_id_302f312d_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_representante_id_b06a7961_fk_personas_id FOREIGN KEY (representante_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_solicitante_id_db9e101d_fk_personas_id FOREIGN KEY (solicitante_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_tipo_procedimiento_t_b1a3d27e_fk_tipos_pro FOREIGN KEY (tipo_procedimiento_tupa_id) REFERENCES public.tipos_procedimiento_tupa(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_usuario_ampliacion_9ca0df94_fk_auth_user_id FOREIGN KEY (usuario_ampliacion) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.expedientes
    ADD CONSTRAINT expedientes_usuario_id_67e66efd_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.feriados_anuales
    ADD CONSTRAINT feriados_anuales_user_id_112e151c_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.giros
    ADD CONSTRAINT giros_usuario_id_289a5cc6_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_archivos
    ADD CONSTRAINT itse_archivos_itse_id_3bd7d6a9_fk_itse_id FOREIGN KEY (itse_id) REFERENCES public.itse(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_archivos
    ADD CONSTRAINT itse_archivos_usuario_id_8ecb0313_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_conductor_id_0c98cf06_fk_personas_id FOREIGN KEY (conductor_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_estados
    ADD CONSTRAINT itse_estados_estado_id_03f10c08_fk_estados_id FOREIGN KEY (estado_id) REFERENCES public.estados(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_estados
    ADD CONSTRAINT itse_estados_itse_id_219803f9_fk_itse_id FOREIGN KEY (itse_id) REFERENCES public.itse(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_estados
    ADD CONSTRAINT itse_estados_usuario_id_402903ca_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_expediente_id_fb44ca9b_fk_expedientes_id FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_giros
    ADD CONSTRAINT itse_giros_giro_id_bd40279b_fk_giros_id FOREIGN KEY (giro_id) REFERENCES public.giros(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_giros
    ADD CONSTRAINT itse_giros_itse_id_3fee7732_fk_itse_id FOREIGN KEY (itse_id) REFERENCES public.itse(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse_giros
    ADD CONSTRAINT itse_giros_usuario_id_3dd6f70e_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_itse_principal_id_6f07d7df_fk_itse_id FOREIGN KEY (itse_principal_id) REFERENCES public.itse(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_nivel_riesgo_id_cb56eaf6_fk_niveles_riesgo_id FOREIGN KEY (nivel_riesgo_id) REFERENCES public.niveles_riesgo(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_titular_id_53721460_fk_personas_id FOREIGN KEY (titular_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.itse
    ADD CONSTRAINT itse_usuario_id_11e05815_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_estados
    ADD CONSTRAINT licencias_funcionami_estado_id_33006461_fk_estados_i FOREIGN KEY (estado_id) REFERENCES public.estados(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionami_expediente_id_f855015f_fk_expedient FOREIGN KEY (expediente_id) REFERENCES public.expedientes(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_archivos
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_27cabff7_fk_licencias FOREIGN KEY (licencia_funcionamiento_id) REFERENCES public.licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_estados
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_95591e9c_fk_licencias FOREIGN KEY (licencia_funcionamiento_id) REFERENCES public.licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_giros
    ADD CONSTRAINT licencias_funcionami_licencia_funcionamie_aa355771_fk_licencias FOREIGN KEY (licencia_funcionamiento_id) REFERENCES public.licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionami_licencia_principal_i_bbaf125a_fk_licencias FOREIGN KEY (licencia_principal_id) REFERENCES public.licencias_funcionamiento(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionami_nivel_riesgo_id_7d2ed6ac_fk_niveles_r FOREIGN KEY (nivel_riesgo_id) REFERENCES public.niveles_riesgo(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionami_tipo_licencia_id_f14736d6_fk_tipos_lic FOREIGN KEY (tipo_licencia_id) REFERENCES public.tipos_licencia(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_giros
    ADD CONSTRAINT licencias_funcionami_usuario_id_0247bb03_fk_auth_user FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_archivos
    ADD CONSTRAINT licencias_funcionami_usuario_id_6d608437_fk_auth_user FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_estados
    ADD CONSTRAINT licencias_funcionami_usuario_id_f5a551eb_fk_auth_user FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionami_zonificacion_id_80234f39_fk_zonificac FOREIGN KEY (zonificacion_id) REFERENCES public.zonificaciones(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_conductor_id_cf87f9e7_fk_personas_id FOREIGN KEY (conductor_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento_giros
    ADD CONSTRAINT licencias_funcionamiento_giros_giro_id_ea847b74_fk_giros_id FOREIGN KEY (giro_id) REFERENCES public.giros(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_titular_id_e760065f_fk_personas_id FOREIGN KEY (titular_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.licencias_funcionamiento
    ADD CONSTRAINT licencias_funcionamiento_usuario_id_5c175952_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.personas_documentos
    ADD CONSTRAINT personas_documentos_persona_id_74a7c7f1_fk_personas_id FOREIGN KEY (persona_id) REFERENCES public.personas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.personas_documentos
    ADD CONSTRAINT personas_documentos_tipo_documento_ident_01a14c84_fk_tipos_doc FOREIGN KEY (tipo_documento_identidad_id) REFERENCES public.tipos_documento_identidad(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.personas
    ADD CONSTRAINT personas_user_id_475c5db3_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.tipos_procedimiento_tupa
    ADD CONSTRAINT tipos_procedimiento__unidad_organica_id_e9dda056_fk_unidades_ FOREIGN KEY (unidad_organica_id) REFERENCES public.unidades_organicas(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.tipos_procedimiento_tupa
    ADD CONSTRAINT tipos_procedimiento_tupa_usuario_id_02b698e5_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.usuarios_perfiles
    ADD CONSTRAINT usuarios_perfiles_user_id_cd3dafe2_fk_auth_user_id FOREIGN KEY (user_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.usuarios_perfiles
    ADD CONSTRAINT usuarios_perfiles_user_id_digitador_42b3baac_fk_auth_user_id FOREIGN KEY (user_id_digitador) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
ALTER TABLE ONLY public.zonificaciones
    ADD CONSTRAINT zonificaciones_usuario_id_f1804683_fk_auth_user_id FOREIGN KEY (usuario_id) REFERENCES public.auth_user(id) DEFERRABLE INITIALLY DEFERRED;
