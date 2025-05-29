-- Tabla de protection stones
CREATE TABLE IF NOT EXISTS protection_stones(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    owner TEXT NOT NULL,
    region_id TEXT NOT NULL UNIQUE,
    world TEXT NOT NULL,
    x INTEGER NOT NULL,
    y INTEGER NOT NULL,
    z INTEGER NOT NULL,
    block_id INTEGER NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de miembros de regiones
CREATE TABLE IF NOT EXISTS ps_members(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    region_id TEXT NOT NULL,
    player TEXT NOT NULL,
    permissions TEXT NOT NULL DEFAULT 'interact,use',
    added_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (region_id) REFERENCES protection_stones(region_id) ON DELETE CASCADE,
    UNIQUE(region_id, player)
);

-- Tabla de flags de regiones
CREATE TABLE IF NOT EXISTS ps_flags(
    id INTEGER PRIMARY KEY AUTOINCREMENT,
    region_id TEXT NOT NULL,
    flag TEXT NOT NULL,
    value TEXT NOT NULL,
    FOREIGN KEY (region_id) REFERENCES protection_stones(region_id) ON DELETE CASCADE,
    UNIQUE(region_id, flag)
);

-- Índices para búsquedas rápidas
CREATE INDEX IF NOT EXISTS idx_ps_location ON protection_stones(world, x, y, z);
CREATE INDEX IF NOT EXISTS idx_ps_owner ON protection_stones(owner);
CREATE INDEX IF NOT EXISTS idx_ps_region ON protection_stones(region_id);
CREATE INDEX IF NOT EXISTS idx_member_region ON ps_members(region_id);
CREATE INDEX IF NOT EXISTS idx_member_player ON ps_members(player);
CREATE INDEX IF NOT EXISTS idx_flag_region ON ps_flags(region_id);

-- Consultas para ProtectionStones

-- Añadir protection stone
CREATE PROCEDURE add_ps(owner TEXT, region_id TEXT, world TEXT, x INTEGER, y INTEGER, z INTEGER, block_id INTEGER)
BEGIN
    INSERT INTO protection_stones(owner, region_id, world, x, y, z, block_id) 
    VALUES(owner, region_id, world, x, y, z, block_id);
END;

-- Obtener todas las protecciones
CREATE PROCEDURE get_all_protections()
BEGIN
    SELECT * FROM protection_stones;
END;

-- Obtener protection stone por bloque
CREATE PROCEDURE get_ps_by_block(x INTEGER, y INTEGER, z INTEGER, world TEXT)
BEGIN
    SELECT * FROM protection_stones 
    WHERE x = x AND y = y AND z = z AND world = world 
    LIMIT 1;
END;

-- Obtener protection stone por ID
CREATE PROCEDURE get_ps_by_id(region_id TEXT)
BEGIN
    SELECT * FROM protection_stones 
    WHERE region_id = region_id 
    LIMIT 1;
END;

-- Eliminar protection stone
CREATE PROCEDURE remove_ps(id INTEGER)
BEGIN
    DELETE FROM protection_stones WHERE id = id;
END;

-- Eliminar todas las regiones de un jugador
CREATE PROCEDURE remove_player_regions(owner TEXT)
BEGIN
    DELETE FROM protection_stones WHERE owner = owner;
END;

-- Consultas para miembros
-- Añadir miembro
CREATE PROCEDURE add_member(region_id TEXT, player TEXT)
BEGIN
    INSERT OR IGNORE INTO ps_members(region_id, player) 
    VALUES(region_id, player);
END;

-- Eliminar miembro
CREATE PROCEDURE remove_member(region_id TEXT, player TEXT)
BEGIN
    DELETE FROM ps_members 
    WHERE region_id = region_id AND player = player;
END;

-- Obtener miembros de región
CREATE PROCEDURE get_region_members(region_id TEXT)
BEGIN
    SELECT * FROM ps_members 
    WHERE region_id = region_id;
END;

-- Verificar si es miembro
CREATE PROCEDURE check_member(region_id TEXT, player TEXT)
BEGIN
    SELECT 1 FROM ps_members 
    WHERE region_id = region_id AND player = player 
    LIMIT 1;
END;

-- Consultas para flags
-- Actualizar flag
CREATE PROCEDURE update_flag(region_id TEXT, flag TEXT, value TEXT)
BEGIN
    INSERT OR REPLACE INTO ps_flags(region_id, flag, value) 
    VALUES(region_id, flag, value);
END;

-- Obtener flags de región
CREATE PROCEDURE get_region_flags(region_id TEXT)
BEGIN
    SELECT * FROM ps_flags 
    WHERE region_id = region_id;
END;

-- Estadísticas
-- Contar regiones totales
CREATE PROCEDURE count_regions()
BEGIN
    SELECT COUNT(*) as count FROM protection_stones;
END;

-- Contar miembros totales
CREATE PROCEDURE count_members()
BEGIN
    SELECT COUNT(*) as count FROM ps_members;
END;

-- Obtener regiones de jugador
CREATE PROCEDURE get_player_regions(owner TEXT)
BEGIN
    SELECT * FROM protection_stones 
    WHERE owner = owner;
END;