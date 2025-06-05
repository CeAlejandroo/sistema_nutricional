#!/usr/bin/env python3
# Script para probar la conexión a MySQL desde Python

import mysql.connector
from mysql.connector import Error
import json

def test_mysql_connection():
    """Probar conexión a MySQL"""
    try:
        # Configuración de conexión (ajusta según tu configuración XAMPP)
        connection = mysql.connector.connect(
            host='localhost',
            database='sistema_nutricional',
            user='root',        # Usuario por defecto de XAMPP
            password=''         # Contraseña vacía por defecto en XAMPP
        )
        
        if connection.is_connected():
            db_info = connection.get_server_info()
            print(f"✅ Conexión exitosa a MySQL Server versión {db_info}")
            
            # Probar consulta simple
            cursor = connection.cursor()
            cursor.execute("SELECT COUNT(*) FROM usuarios")
            result = cursor.fetchone()
            print(f"✅ Usuarios en la base de datos: {result[0]}")
            
            # Probar consulta de alimentos (si existe la tabla)
            try:
                cursor.execute("SELECT COUNT(*) FROM alimentos")
                result = cursor.fetchone()
                print(f"✅ Alimentos en la base de datos: {result[0]}")
            except:
                print("ℹ️  Tabla 'alimentos' no existe aún (normal si no has ejecutado los scripts)")
            
            cursor.close()
            
        return True
        
    except Error as e:
        print(f"❌ Error conectando a MySQL: {e}")
        print("💡 Asegúrate de que XAMPP esté ejecutándose y MySQL activo")
        return False
        
    finally:
        if 'connection' in locals() and connection.is_connected():
            connection.close()
            print("✅ Conexión MySQL cerrada")

def test_json_processing():
    """Probar procesamiento de JSON"""
    try:
        # Datos de ejemplo como los que enviará PHP
        test_data = {
            'edad': 25,
            'genero': 'M',
            'peso': 70.5,
            'altura': 175,
            'nivel_actividad': 'moderado',
            'objetivo': 'mantener_peso'
        }
        
        # Convertir a JSON y de vuelta (simula el proceso PHP->Python)
        json_string = json.dumps(test_data)
        parsed_data = json.loads(json_string)
        
        print("✅ Procesamiento JSON exitoso")
        print(f"Datos de prueba: {parsed_data}")
        
        return True
        
    except Exception as e:
        print(f"❌ Error en procesamiento JSON: {e}")
        return False

if __name__ == "__main__":
    print("🔍 Probando dependencias de Python para sistema nutricional...")
    print("-" * 60)
    
    # Probar JSON
    print("1. Probando procesamiento JSON:")
    json_ok = test_json_processing()
    print()
    
    # Probar MySQL
    print("2. Probando conexión a MySQL:")
    mysql_ok = test_mysql_connection()
    print()
    
    if json_ok and mysql_ok:
        print("🎉 ¡Todas las dependencias están funcionando correctamente!")
        print("✅ Listo para integrar PHP con Python")
    else:
        print("⚠️  Hay problemas con las dependencias. Revisa los errores arriba.")
