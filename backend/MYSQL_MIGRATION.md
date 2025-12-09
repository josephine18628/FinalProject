# MySQL Migration Guide

The backend has been successfully migrated from PostgreSQL to MySQL (XAMPP compatible).

## Changes Made

### 1. Database Driver
- **Removed**: `psycopg2-binary`
- **Added**: `pymysql`

### 2. Database URL Format
Update your `.env` file:
```
DATABASE_URL=mysql+pymysql://root:@localhost:3306/cs3
```

**XAMPP Default Settings:**
- Host: `localhost`
- Port: `3306`
- Username: `root`
- Password: (empty by default)
- Database: `cs3` (create in phpMyAdmin)

**With Password:**
```
DATABASE_URL=mysql+pymysql://root:yourpassword@localhost:3306/cs3
```

### 3. Model Changes

All UUID columns changed from:
```python
from sqlalchemy.dialects.postgresql import UUID
id = Column(UUID(as_uuid=True), primary_key=True, default=uuid.uuid4)
```

To:
```python
from sqlalchemy import CHAR
id = Column(CHAR(36), primary_key=True, default=lambda: str(uuid.uuid4()))
```

### 4. DateTime Changes
Removed timezone support:
```python
# Before: DateTime(timezone=True)
# After: DateTime(timezone=False)
```

### 5. Search/Query Changes
- Replaced `ilike()` with `func.lower()` for case-insensitive searches
- MySQL uses LIKE with case-insensitive collation

## Setup Instructions

### Step 1: Install Dependencies
```bash
cd backend
pip install -r requirements.txt
```

### Step 2: Create Database in XAMPP
1. Start XAMPP and start MySQL service
2. Open phpMyAdmin: http://localhost/phpmyadmin
3. Click "New" to create database
4. Name: `cs3`
5. Collation: `utf8mb4_unicode_ci`
6. Click "Create"

### Step 3: Configure Environment
1. Copy `.env.example` to `.env` (if not exists)
2. Update `DATABASE_URL` with your MySQL credentials
3. Add your API keys

### Step 4: Run Migrations

**Option A: Using Alembic (Recommended)**
```bash
# Generate initial migration
alembic revision --autogenerate -m "Initial MySQL schema"

# Apply migrations
alembic upgrade head
```

**Option B: Using create_all() (Simpler)**
```python
# Add to backend/app/main.py temporarily:
from app.database import engine, Base
from app.models import *
Base.metadata.create_all(bind=engine)
```

Then run your FastAPI server once to create tables.

### Step 5: Test Connection
```bash
# Start FastAPI
uvicorn app.main:app --reload
```

Check http://localhost:8000/health - should return `{"status": "healthy"}`

## Important Notes

1. **UUID Storage**: UUIDs are now stored as CHAR(36) strings instead of native UUID type
2. **Case Sensitivity**: MySQL searches are case-insensitive by default with utf8mb4_ci collation
3. **JSON Support**: Requires MySQL 5.7+ or MariaDB 10.2+ (XAMPP includes compatible versions)
4. **No Timezone**: DateTime columns don't store timezone information

## Troubleshooting

### Connection Error
- Verify MySQL is running in XAMPP
- Check port 3306 is not blocked
- Verify database name matches in .env

### Table Creation Error
- Ensure database exists in phpMyAdmin
- Check user has CREATE TABLE permissions
- Verify charset is utf8mb4

### Import Error (pymysql)
```bash
pip install pymysql
```

### JSON Column Error (MySQL < 5.7)
If using older MySQL, change JSON columns to TEXT:
```python
# In models, change:
correct_answer = Column(JSON, ...)
# To:
correct_answer = Column(Text, ...)
# Then parse manually: json.loads(question.correct_answer)
```

## Verification

Test the setup:
```python
# backend/test_mysql.py
from app.database import engine
from sqlalchemy import text

try:
    with engine.connect() as conn:
        result = conn.execute(text("SELECT VERSION()"))
        print(f"✓ Connected! MySQL version: {result.fetchone()[0]}")
        
        # Check if tables exist
        result = conn.execute(text("SHOW TABLES"))
        tables = [row[0] for row in result]
        print(f"✓ Tables found: {len(tables)}")
        print(f"  {', '.join(tables)}")
except Exception as e:
    print(f"✗ Error: {e}")
```

Run: `python test_mysql.py`

