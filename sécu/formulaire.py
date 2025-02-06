import tkinter as tk
from tkinter import messagebox
import sqlite3
import hashlib

#Configuration de la base de données
connexion = sqlite3.connect('utilisateurs.db')
curseur = connexion.cursor()
curseur.execute('''CREATE TABLE IF NOT EXISTS utilisateurs (
                    identifiant TEXT PRIMARY KEY,
                    mot_de_passe TEXT NOT NULL)''')
connexion.commit()

#Fonction de hachage
def hacher_mot_de_passe(mot_de_passe):
    return hashlib.sha256(mot_de_passe.encode()).hexdigest()

#Configuration de l'interface
fenetre = tk.Tk()
fenetre.title("Formulaire d'Identification")
fenetre.geometry("300x400")

#Fonctions
def valider():
    identifiant = entree_identifiant.get()
    mot_de_passe = hacher_mot_de_passe(entree_mot_de_passe.get())

    curseur.execute("SELECT * FROM utilisateurs WHERE identifiant = ? AND mot_de_passe = ?", (identifiant, mot_de_passe))
    if curseur.fetchone():
        messagebox.showinfo("Succès", "Vous êtes connecté(e)")
    else:
        messagebox.showerror("Erreur", "Identifiant ou mot de passe incorrect. Veuillez réessayer.")

def reinitialiser():
    entree_identifiant.delete(0, tk.END)
    entree_mot_de_passe.delete(0, tk.END)

def ajouter_compte():
    identifiant = entree_identifiant.get()
    mot_de_passe = hacher_mot_de_passe(entree_mot_de_passe.get())

    if identifiant and mot_de_passe:
        try:
            curseur.execute("INSERT INTO utilisateurs (identifiant, mot_de_passe) VALUES (?, ?)", (identifiant, mot_de_passe))
            connexion.commit()
            messagebox.showinfo("Succès", "Compte ajouté avec succès.")
        except sqlite3.IntegrityError:
            messagebox.showerror("Erreur", "L'identifiant existe déjà.")
    else:
        messagebox.showwarning("Attention", "Veuillez remplir les deux champs avant d'ajouter un compte.")

#Logol
cadre_logo = tk.Frame(fenetre)
cadre_logo.pack(anchor="n", pady=10)

logo = tk.PhotoImage(file="logo.png")
etiquette_logo = tk.Label(cadre_logo, image=logo)
etiquette_logo.pack()
etiquette_logo.config(width=250, height=220)

cadre_formulaire = tk.Frame(fenetre)
cadre_formulaire.pack(pady=10)

tk.Label(cadre_formulaire, text="Identifiant :").grid(row=0, column=0, sticky=tk.W, pady=5)
entree_identifiant = tk.Entry(cadre_formulaire)
entree_identifiant.grid(row=0, column=1, pady=5)

tk.Label(cadre_formulaire, text="Mot de passe :").grid(row=1, column=0, sticky=tk.W, pady=5)
entree_mot_de_passe = tk.Entry(cadre_formulaire, show="*")
entree_mot_de_passe.grid(row=1, column=1, pady=5)

#Boutons
cadre_boutons = tk.Frame(fenetre)
cadre_boutons.pack(pady=10)

tk.Button(cadre_boutons, text="Réinitialiser", command=reinitialiser).grid(row=0, column=0, padx=5)
tk.Button(cadre_boutons, text="Valider", command=valider).grid(row=0, column=1, padx=5)
tk.Button(cadre_boutons, text="Ajouter un compte", command=ajouter_compte).grid(row=0, column=2, padx=5)

fenetre.mainloop()

#Fermeture de la connexion à la base de données
connexion.close()