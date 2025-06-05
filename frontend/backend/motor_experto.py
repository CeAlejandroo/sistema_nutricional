import sys

edad = int(sys.argv[1])
peso = float(sys.argv[2])
objetivo = sys.argv[3]

if objetivo == "subir masa":
    if peso < 60:
        print("Plan A: Dieta hipercalórica + 5 comidas al día")
    else:
        print("Plan B: Dieta con alto contenido proteico")
elif objetivo == "bajar grasa":
    print("Plan C: Déficit calórico + baja en carbohidratos")
else:
    print("Plan D: Mantenimiento con balance nutricional")
