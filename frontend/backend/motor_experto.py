#!/usr/bin/env python3
# Ejemplo de cómo debería estructurarse el motor_experto.py

import json
import sys
import mysql.connector
from mysql.connector import Error
import os

def conectar_bd():
    """Conectar a la base de datos MySQL"""
    try:
        connection = mysql.connector.connect(
            host='localhost',
            database='sistema_nutricional',
            user='root',  # Cambiar por tu usuario
            password=''   # Cambiar por tu contraseña
        )
        return connection
    except Error as e:
        print(f"Error conectando a MySQL: {e}")
        return None

def calcular_calorias_basales(edad, genero, peso, altura):
    """Calcular metabolismo basal usando fórmula Harris-Benedict"""
    if genero == 'M':
        bmr = 88.362 + (13.397 * peso) + (4.799 * altura) - (5.677 * edad)
    else:
        bmr = 447.593 + (9.247 * peso) + (3.098 * altura) - (4.330 * edad)
    
    return bmr

def calcular_calorias_totales(bmr, nivel_actividad):
    """Calcular calorías totales según nivel de actividad"""
    factores = {
        'sedentario': 1.2,
        'ligero': 1.375,
        'moderado': 1.55,
        'intenso': 1.725,
        'muy_intenso': 1.9
    }
    
    return bmr * factores.get(nivel_actividad, 1.2)

def ajustar_por_objetivo(calorias, objetivo):
    """Ajustar calorías según objetivo"""
    if objetivo == 'perder_peso':
        return calorias - 500  # Déficit de 500 cal
    elif objetivo == 'ganar_peso':
        return calorias + 500  # Superávit de 500 cal
    elif objetivo == 'ganar_musculo':
        return calorias + 300  # Superávit moderado
    else:
        return calorias  # Mantener peso

def obtener_alimentos(connection):
    """Obtener alimentos de la base de datos"""
    cursor = connection.cursor(dictionary=True)
    cursor.execute("SELECT * FROM alimentos")
    alimentos = cursor.fetchall()
    cursor.close()
    return alimentos

def generar_plan_nutricional(datos_paciente):
    """Generar plan nutricional basado en datos del paciente"""
    
    # Calcular requerimientos calóricos
    bmr = calcular_calorias_basales(
        datos_paciente['edad'],
        datos_paciente['genero'],
        datos_paciente['peso'],
        datos_paciente['altura']
    )
    
    calorias_totales = calcular_calorias_totales(bmr, datos_paciente['nivel_actividad'])
    calorias_objetivo = ajustar_por_objetivo(calorias_totales, datos_paciente['objetivo'])
    
    # Distribución de macronutrientes (ejemplo básico)
    proteinas_calorias = calorias_objetivo * 0.25  # 25% proteínas
    carbohidratos_calorias = calorias_objetivo * 0.45  # 45% carbohidratos
    grasas_calorias = calorias_objetivo * 0.30  # 30% grasas
    
    # Convertir a gramos
    proteinas_gramos = proteinas_calorias / 4  # 4 cal/g
    carbohidratos_gramos = carbohidratos_calorias / 4  # 4 cal/g
    grasas_gramos = grasas_calorias / 9  # 9 cal/g
    
    # Conectar a BD y obtener alimentos
    connection = conectar_bd()
    if not connection:
        return {'success': False, 'error': 'Error de conexión a BD'}
    
    alimentos = obtener_alimentos(connection)
    
    # Lógica simple de selección de alimentos (aquí iría tu lógica experta)
    comidas = []
    
    # Ejemplo básico de distribución por comidas
    calorias_por_comida = {
        'Desayuno': calorias_objetivo * 0.25,
        'Almuerzo': calorias_objetivo * 0.35,
        'Cena': calorias_objetivo * 0.30,
        'Snack': calorias_objetivo * 0.10
    }
    
    for tipo_comida, calorias_comida in calorias_por_comida.items():
        tipo_normalizado = tipo_comida.lower()
        if tipo_normalizado.startswith('snack'):
            tipo_normalizado = 'snack'
        # Seleccionar alimentos para esta comida (lógica simplificada)
        for alimento in alimentos[:3]:  # Ejemplo: tomar primeros 3 alimentos
            cantidad = (calorias_comida / 3) / float(alimento['calorias_por_100g']) * 100
            
            comidas.append({
                "tipo_comida": tipo_normalizado,
                "alimento_id": alimento['id'],
                "cantidad_gramos": cantidad,
                "calorias": calorias_comida
            })
    
    connection.close()
    
    return {
        'success': True,
        'calorias_totales': round(calorias_objetivo),
        'proteinas_totales': round(proteinas_gramos, 2),
        'carbohidratos_totales': round(carbohidratos_gramos, 2),
        'grasas_totales': round(grasas_gramos, 2),
        'comidas': comidas
    }

def main():
    """Función principal"""
    if len(sys.argv) != 2:
        print(json.dumps({'success': False, 'error': 'Faltan argumentos'}))
        return
    
    tempFile = sys.argv[1]
    
    # Verificar si el archivo temporal existe
    if not os.path.exists(tempFile):
        with open(os.path.join(os.path.dirname(__file__), 'debug_motor.txt'), 'w', encoding='utf-8') as debug_file:
            debug_file.write('Archivo temporal no existe: ' + tempFile)
        print(json.dumps({'success': False, 'error': 'Archivo temporal no existe'}))
        return
    
    # Leer datos del archivo temporal
    try:
        with open(tempFile, 'r', encoding='utf-8') as f:
            datos_paciente = json.load(f)
    except Exception as e:
        print(json.dumps({'success': False, 'error': f'Error leyendo datos: {str(e)}'}))
        return
    
    # Generar plan nutricional
    resultado = generar_plan_nutricional(datos_paciente)
    
    # Devolver resultado como JSON
    print(json.dumps(resultado))

if __name__ == "__main__":
    main()
