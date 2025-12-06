# 🌳 GIT WORKFLOW - Guía Completa

Esta guía documenta el flujo de trabajo Git para el desarrollo del proyecto.

---

## 📊 ESTRATEGIA DE RAMAS

```
main (producción - protegida)
│
├── develop (desarrollo principal - protegida)
│   │
│   ├── sprint-01/refactoring-architecture
│   ├── sprint-02/vue-components-testing
│   ├── sprint-03/financial-transactions-dashboard
│   ├── sprint-04/expense-management
│   ├── sprint-05/statistics-reports
│   ├── sprint-06/customers-receivables
│   ├── sprint-07/suppliers-payables
│   ├── sprint-08/table-management
│   ├── sprint-09/table-management-integration
│   ├── sprint-10/cash-register-employees
│   ├── sprint-11/settings-catalog
│   └── sprint-12/optimization-documentation
│
├── feature/* (features individuales)
├── bugfix/* (corrección de bugs)
└── hotfix/* (correcciones urgentes en producción)
```

---

## 🚀 COMANDOS ESENCIALES

### 1. Configuración Inicial

```bash
# Configurar usuario Git (si aún no lo has hecho)
git config --global user.name "Tu Nombre"
git config --global user.email "tu@email.com"

# Configurar editor por defecto
git config --global core.editor "code --wait"

# Configurar colores
git config --global color.ui auto

# Ver configuración actual
git config --list
```

### 2. Crear Rama Develop (Una sola vez)

```bash
# Asegurarse de estar en main actualizada
git checkout main
git pull origin main

# Crear rama develop desde main
git checkout -b develop

# Pushear develop a remoto
git push -u origin develop

# Configurar rama develop como rama principal de desarrollo
git branch --set-upstream-to=origin/develop develop
```

### 3. Iniciar un Nuevo Sprint

```bash
# Asegurarse de estar en develop actualizada
git checkout develop
git pull origin develop

# Crear rama del sprint
git checkout -b sprint-XX/nombre-descriptivo

# Ejemplo para Sprint 1:
git checkout -b sprint-01/refactoring-architecture

# Pushear rama al remoto
git push -u origin sprint-01/refactoring-architecture

# Verificar rama actual
git branch
```

### 4. Trabajo Diario en el Sprint

#### Commits Frecuentes
```bash
# Ver estado actual
git status

# Ver cambios no staged
git diff

# Agregar archivos específicos
git add app/Repositories/ProductRepository.php
git add app/Services/SaleService.php

# O agregar todos los cambios
git add .

# Commit con mensaje descriptivo
git commit -m "feat: Implementar ProductRepository con métodos CRUD"

# Push a rama del sprint
git push origin sprint-01/refactoring-architecture
```

#### Convención de Mensajes de Commit

```bash
# Formato: <tipo>: <descripción>

# Tipos:
feat: Nueva funcionalidad
fix: Corrección de bug
docs: Cambios en documentación
style: Formato, punto y coma faltante, etc (sin cambios de código)
refactor: Refactorización de código
test: Agregar tests faltantes
chore: Mantenimiento (actualizar dependencias, etc)

# Ejemplos:
git commit -m "feat: Agregar ProductRepository con métodos CRUD"
git commit -m "fix: Corregir cálculo de stock disponible en MenuItem"
git commit -m "docs: Actualizar README con instrucciones de testing"
git commit -m "test: Agregar tests unitarios para SaleService"
git commit -m "refactor: Extraer lógica de inventario a InventoryService"
git commit -m "chore: Actualizar dependencias de composer"
```

### 5. Sincronizar con Develop

```bash
# Si otros desarrolladores han hecho cambios en develop
git checkout develop
git pull origin develop

# Volver a tu rama del sprint
git checkout sprint-01/refactoring-architecture

# Integrar cambios de develop
git merge develop

# O usando rebase (más limpio)
git rebase develop

# Resolver conflictos si existen
# ... editar archivos en conflicto ...
git add .
git rebase --continue

# Push (si usaste rebase, necesitas force push)
git push origin sprint-01/refactoring-architecture --force-with-lease
```

### 6. Finalizar Sprint y Crear PR

```bash
# Asegurarse de que todo está committeado
git status

# Ejecutar tests
php artisan test
npm run test

# Ejecutar Pint
./vendor/bin/pint

# Último commit si hay cambios
git add .
git commit -m "chore: Ejecutar Laravel Pint antes de PR"

# Push final
git push origin sprint-01/refactoring-architecture

# Crear Pull Request en GitHub
# (se abrirá automáticamente con el template)
```

### 7. Después del Merge a Develop

```bash
# Ir a develop
git checkout develop

# Actualizar develop
git pull origin develop

# Eliminar rama del sprint localmente
git branch -d sprint-01/refactoring-architecture

# Eliminar rama del sprint en remoto (opcional)
git push origin --delete sprint-01/refactoring-architecture

# Prepararse para el siguiente sprint
git checkout -b sprint-02/vue-components-testing
git push -u origin sprint-02/vue-components-testing
```

---

## 🚨 COMANDOS DE EMERGENCIA

### Deshacer Cambios No Committeados

```bash
# Descartar cambios en un archivo específico
git checkout -- archivo.php

# Descartar TODOS los cambios no committeados
git checkout .

# Eliminar archivos no rastreados
git clean -fd
```

### Deshacer Último Commit (sin perder cambios)

```bash
# Deshacer último commit pero mantener cambios
git reset --soft HEAD~1

# Deshacer último commit y descartar cambios
git reset --hard HEAD~1

# Deshacer varios commits
git reset --soft HEAD~3  # Últimos 3 commits
```

### Corregir Mensaje del Último Commit

```bash
# Editar mensaje del último commit
git commit --amend -m "Mensaje corregido"

# Si ya hiciste push
git push origin sprint-01/refactoring-architecture --force-with-lease
```

### Resolver Conflictos

```bash
# Ver archivos en conflicto
git status

# Abrir archivo y resolver manualmente
# Buscar marcadores: <<<<<<<, =======, >>>>>>>

# Después de resolver
git add archivo-resuelto.php

# Continuar con merge/rebase
git merge --continue
# o
git rebase --continue

# Si quieres abortar el merge/rebase
git merge --abort
# o
git rebase --abort
```

### Stash (Guardar Cambios Temporalmente)

```bash
# Guardar cambios actuales sin commit
git stash

# Listar stashes guardados
git stash list

# Aplicar último stash
git stash pop

# Aplicar stash específico
git stash apply stash@{0}

# Eliminar último stash
git stash drop

# Eliminar todos los stashes
git stash clear
```

---

## 🔧 WORKFLOWS COMUNES

### Workflow 1: Iniciar Nuevo Sprint

```bash
#!/bin/bash
# Script: start-sprint.sh

SPRINT_NUMBER=$1
SPRINT_NAME=$2

if [ -z "$SPRINT_NUMBER" ] || [ -z "$SPRINT_NAME" ]; then
    echo "Uso: ./start-sprint.sh <número> <nombre>"
    echo "Ejemplo: ./start-sprint.sh 01 refactoring-architecture"
    exit 1
fi

git checkout develop
git pull origin develop
git checkout -b sprint-${SPRINT_NUMBER}/${SPRINT_NAME}
git push -u origin sprint-${SPRINT_NUMBER}/${SPRINT_NAME}

echo "✅ Sprint ${SPRINT_NUMBER} iniciado en rama sprint-${SPRINT_NUMBER}/${SPRINT_NAME}"
```

### Workflow 2: Daily Commit

```bash
#!/bin/bash
# Script: daily-commit.sh

# Ejecutar Pint
echo "🎨 Ejecutando Laravel Pint..."
./vendor/bin/pint

# Agregar cambios
git add .

# Pedir mensaje de commit
echo "📝 Mensaje de commit:"
read COMMIT_MSG

# Commit
git commit -m "$COMMIT_MSG"

# Push
BRANCH=$(git branch --show-current)
git push origin $BRANCH

echo "✅ Cambios pusheados a $BRANCH"
```

### Workflow 3: Pre-PR Checklist

```bash
#!/bin/bash
# Script: pre-pr.sh

echo "🔍 Verificando código antes de PR..."

# Ejecutar Pint
echo "1️⃣ Ejecutando Laravel Pint..."
./vendor/bin/pint
if [ $? -ne 0 ]; then
    echo "❌ Error en Pint"
    exit 1
fi

# Ejecutar tests
echo "2️⃣ Ejecutando tests de PHP..."
php artisan test
if [ $? -ne 0 ]; then
    echo "❌ Tests fallando"
    exit 1
fi

# Ejecutar tests de Vue
echo "3️⃣ Ejecutando tests de Vue..."
npm run test
if [ $? -ne 0 ]; then
    echo "❌ Tests de Vue fallando"
    exit 1
fi

# Verificar que no haya cambios sin committear
if [ -n "$(git status --porcelain)" ]; then
    echo "⚠️  Hay cambios sin committear"
    git status
    exit 1
fi

echo "✅ Todo listo para crear PR!"
```

---

## 📝 TEMPLATES DE COMMIT

### Feature Completa
```bash
git commit -m "feat: Implementar sistema de autenticación

- Agregar Login/Registro con Laravel Breeze
- Implementar middleware de autenticación
- Crear vistas de login y registro
- Agregar tests de autenticación

Closes #123"
```

### Bug Fix
```bash
git commit -m "fix: Corregir deducción de inventario en ventas

El sistema no estaba deduciendo correctamente el stock
cuando se vendían productos compuestos (menu items).

- Corregir lógica en POSController
- Agregar validación de stock antes de venta
- Agregar test para verificar deducción correcta

Fixes #456"
```

### Refactorización
```bash
git commit -m "refactor: Extraer lógica de ventas a SaleService

- Crear SaleService con métodos de negocio
- Mover lógica de POSController a SaleService
- Simplificar controlador usando service
- Agregar tests unitarios para SaleService"
```

---

## 🎯 MEJORES PRÁCTICAS

### ✅ DO (Hacer)

1. **Commits pequeños y frecuentes**
   ```bash
   # Mejor: varios commits pequeños
   git commit -m "feat: Crear ProductRepository"
   git commit -m "test: Agregar tests para ProductRepository"
   git commit -m "docs: Documentar métodos de ProductRepository"
   ```

2. **Mensajes descriptivos**
   ```bash
   # Bien ✅
   git commit -m "feat: Implementar búsqueda de productos por categoría"

   # Mal ❌
   git commit -m "cambios"
   git commit -m "fix"
   git commit -m "WIP"
   ```

3. **Pull antes de push**
   ```bash
   git pull origin develop
   git push origin sprint-01/refactoring-architecture
   ```

4. **Revisar cambios antes de commit**
   ```bash
   git diff
   git status
   ```

### ❌ DON'T (No hacer)

1. **No hacer commit de archivos sensibles**
   ```bash
   # Asegurarse de que .env está en .gitignore
   git add .env  # ❌ NUNCA
   ```

2. **No hacer force push a main o develop**
   ```bash
   git push origin main --force  # ❌ NUNCA
   git push origin develop --force  # ❌ NUNCA
   ```

3. **No commitear código que no funciona**
   ```bash
   # Siempre verificar antes de commit
   php artisan test
   npm run test
   ```

4. **No hacer commits gigantes**
   ```bash
   # Evitar commits con 50+ archivos modificados
   # Dividir en commits lógicos más pequeños
   ```

---

## 🔒 PROTECCIÓN DE RAMAS

### Configurar Branch Protection en GitHub

Para las ramas `main` y `develop`:

1. Ir a Settings → Branches → Add rule
2. Branch name pattern: `main` o `develop`
3. Activar:
   - ✅ Require pull request reviews before merging
   - ✅ Require status checks to pass before merging
   - ✅ Require branches to be up to date before merging
   - ✅ Include administrators
   - ✅ Restrict who can push to matching branches

---

## 📊 COMANDOS ÚTILES DE INFORMACIÓN

```bash
# Ver historial de commits
git log --oneline --graph --all

# Ver quién modificó cada línea de un archivo
git blame archivo.php

# Ver cambios entre ramas
git diff develop..sprint-01/refactoring-architecture

# Ver archivos modificados en un commit
git show --name-only COMMIT_HASH

# Buscar en historial de commits
git log --grep="ProductRepository"

# Ver estadísticas de commits
git shortlog -sn

# Ver tamaño del repositorio
git count-objects -vH
```

---

## 🚀 ALIAS ÚTILES

Agregar a `~/.gitconfig`:

```ini
[alias]
    # Shortcuts
    co = checkout
    br = branch
    ci = commit
    st = status

    # Logs bonitos
    lg = log --graph --pretty=format:'%Cred%h%Creset -%C(yellow)%d%Creset %s %Cgreen(%cr) %C(bold blue)<%an>%Creset' --abbrev-commit

    # Ver último commit
    last = log -1 HEAD --stat

    # Listar branches
    branches = branch -a

    # Deshacer último commit
    undo = reset --soft HEAD~1

    # Limpiar ramas ya mergeadas
    cleanup = !git branch --merged | grep -v '\\*\\|master\\|develop' | xargs -n 1 git branch -d
```

Usar:
```bash
git lg  # Ver log bonito
git last  # Ver último commit
git undo  # Deshacer último commit
```

---

## 📚 RECURSOS

- [Git Documentation](https://git-scm.com/doc)
- [GitHub Flow](https://guides.github.com/introduction/flow/)
- [Conventional Commits](https://www.conventionalcommits.org/)
- [Git Cheat Sheet](https://education.github.com/git-cheat-sheet-education.pdf)

---

**Última actualización:** 2025-11-18
