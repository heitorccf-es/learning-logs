"use client";

import { useState, useEffect, useCallback } from "react";
import Image from "next/image";
import { searchRecipes } from "@/services/api";
import { Recipe, PaginationMeta } from "@/types";

const COMMON_INGREDIENTS = [
    { term: "Chicken", label: "Frango" },
    { term: "Rice", label: "Arroz" },
    { term: "Eggs", label: "Ovos" },
    { term: "Milk", label: "Leite" },
    { term: "Flour", label: "Farinha" },
    { term: "Garlic", label: "Alho" },
    { term: "Onion", label: "Cebola" },
    { term: "Potato", label: "Batata" },
    { term: "Tomato", label: "Tomate" },
    { term: "Cheese", label: "Queijo" },
    { term: "Butter", label: "Manteiga" },
    { term: "Pasta", label: "Macarrão" },
];

export default function Home() {
    const [ingredientInput, setIngredientInput] = useState("");
    const [ingredients, setIngredients] = useState<string[]>([]);

    // Estados de Dados e Paginação
    const [recipes, setRecipes] = useState<Recipe[]>([]);
    const [meta, setMeta] = useState<PaginationMeta | null>(null);

    const [loading, setLoading] = useState(false);
    const [hasSearched, setHasSearched] = useState(false);

    // Adiciona ingrediente
    const addIngredient = (value: string) => {
        const trimmed = value.trim();
        // Evita duplicados (case insensitive)
        const exists = ingredients.some((ing) => ing.toLowerCase() === trimmed.toLowerCase());

        if (trimmed && !exists) {
            setIngredients((prev) => [...prev, trimmed]);
            setIngredientInput("");
        }
    };

    const handleManualAdd = () => {
        addIngredient(ingredientInput);
    };

    const removeIngredient = (index: number) => {
        setIngredients(ingredients.filter((_, i) => i !== index));
    };

    // Função de busca (memoizada para usar no useEffect)
    const fetchPage = useCallback(async (page: number, currentIngredients: string[]) => {
        if (currentIngredients.length === 0) {
            setRecipes([]);
            setHasSearched(false);
            return;
        }

        setLoading(true);

        try {
            const result = await searchRecipes(currentIngredients, page);
            setRecipes(result.data);
            setMeta(result.meta);
            setHasSearched(true);

            if (page > 1) {
                window.scrollTo({ top: 0, behavior: "smooth" });
            }
        } catch (error) {
            console.error(error);
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        const timeoutId = setTimeout(() => {
            if (ingredients.length > 0) {
                fetchPage(1, ingredients);
            } else {
                setRecipes([]);
                setHasSearched(false);
            }
        }, 500); // Pequeno delay (debounce) para não buscar enquanto digita muito rápido

        return () => clearTimeout(timeoutId);
    }, [ingredients, fetchPage]);

    // Função auxiliar para verificar match visual
    const isIngredientAvailable = (recipeLine: string) => {
        return ingredients.some((userIng) => recipeLine.toLowerCase().includes(userIng.toLowerCase()));
    };

    return (
        <main className="min-h-screen bg-gray-50 p-8 font-sans">
            <div className="max-w-7xl mx-auto grid grid-cols-1 lg:grid-cols-3 gap-8">
                {/* COLUNA ESQUERDA - PRINCIPAL */}
                <div className="lg:col-span-2 space-y-8">
                    {/* Área de Busca */}
                    <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100">
                        {/* Aviso sobre Inglês */}
                        <div className="bg-yellow-50 border-l-4 border-yellow-400 p-4 mb-6 rounded-r-lg">
                            <div className="flex">
                                <div className="flex-shrink-0">
                                    <svg className="h-5 w-5 text-yellow-400" viewBox="0 0 20 20" fill="currentColor">
                                        <path
                                            fillRule="evenodd"
                                            d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                                            clipRule="evenodd"
                                        />
                                    </svg>
                                </div>
                                <div className="ml-3">
                                    <p className="text-sm text-yellow-700">
                                        <span className="font-bold">Atenção:</span> Para ingredientes manuais, digite em{" "}
                                        <strong>Inglês</strong> (ex: Beef, Carrot). O banco de dados é internacional.
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div className="flex gap-3 mb-6">
                            <input
                                type="text"
                                value={ingredientInput}
                                onChange={(e) => setIngredientInput(e.target.value)}
                                onKeyDown={(e) => e.key === "Enter" && handleManualAdd()}
                                placeholder="O que você tem aí? (Digite em Inglês...)"
                                className="flex-1 p-4 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 transition-all"
                            />

                            <button
                                onClick={handleManualAdd}
                                className="w-16 flex items-center justify-center bg-gray-900 text-white rounded-xl hover:bg-black transition-transform active:scale-95 shadow-md"
                                title="Adicionar Ingrediente"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    strokeWidth={2}
                                    stroke="currentColor"
                                    className="w-8 h-8"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                </svg>
                            </button>
                        </div>

                        {/* Lista de Tags */}
                        <div className="flex flex-wrap gap-2 min-h-[40px]">
                            {ingredients.length === 0 && (
                                <span className="text-gray-400 text-sm py-2 italic">
                                    Adicione ingredientes para buscar automaticamente...
                                </span>
                            )}
                            {ingredients.map((ing, idx) => (
                                <span
                                    key={idx}
                                    className="pl-3 pr-2 py-1.5 bg-green-100 text-green-800 rounded-lg text-sm font-medium flex items-center gap-2 animate-fadeIn"
                                >
                                    {ing}
                                    <button
                                        onClick={() => removeIngredient(idx)}
                                        className="hover:text-red-600 transition-colors"
                                    >
                                        <svg
                                            xmlns="http://www.w3.org/2000/svg"
                                            viewBox="0 0 20 20"
                                            fill="currentColor"
                                            className="w-4 h-4"
                                        >
                                            <path d="M6.28 5.22a.75.75 0 00-1.06 1.06L8.94 10l-3.72 3.72a.75.75 0 101.06 1.06L10 11.06l3.72 3.72a.75.75 0 101.06-1.06L11.06 10l3.72-3.72a.75.75 0 00-1.06-1.06L10 8.94 6.28 5.22z" />
                                        </svg>
                                    </button>
                                </span>
                            ))}
                        </div>

                        {/* Loading Bar se estiver carregando automaticamente */}
                        {loading && (
                            <div className="w-full bg-gray-200 h-1 mt-6 rounded-full overflow-hidden">
                                <div className="bg-green-500 h-1 rounded-full animate-progress"></div>
                            </div>
                        )}
                    </div>

                    {/* Resultados e Paginação */}
                    {hasSearched && recipes.length === 0 && !loading ? (
                        <div className="text-center py-12 bg-white rounded-xl border border-dashed border-gray-300">
                            <p className="text-gray-500 text-lg">Nenhuma receita encontrada.</p>
                            <p className="text-gray-400 text-sm">
                                Tente remover alguns ingredientes para ampliar a busca.
                            </p>
                        </div>
                    ) : (
                        <>
                            {/* GRID DE RECEITAS */}
                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                {recipes.map((recipe) => (
                                    <div
                                        key={recipe.id}
                                        className="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden flex flex-col h-full animate-fadeIn transition-shadow hover:shadow-md"
                                    >
                                        <div className="relative h-48 w-full bg-gray-200">
                                            <Image
                                                src={recipe.image}
                                                alt={recipe.title}
                                                fill
                                                className="object-cover"
                                                sizes="(max-width: 768px) 100vw, 50vw"
                                            />
                                            <div className="absolute inset-0 bg-gradient-to-t from-black/60 to-transparent"></div>
                                            <h3 className="absolute bottom-3 left-4 right-4 text-white font-bold text-lg leading-tight shadow-black drop-shadow-md">
                                                {recipe.title}
                                            </h3>
                                        </div>

                                        <div className="p-5 flex-1 flex flex-col">
                                            <div className="flex justify-between items-center mb-3">
                                                <span className="text-xs font-bold text-gray-400 uppercase">
                                                    Ingredientes
                                                </span>
                                                <span
                                                    className="text-xs bg-gray-100 text-gray-600 px-2 py-0.5 rounded-full"
                                                    title="Ingredientes que você tem"
                                                >
                                                    Compatíveis:{" "}
                                                    {recipe.ingredients.filter((i) => isIngredientAvailable(i)).length}
                                                </span>
                                            </div>

                                            <ul className="text-sm space-y-1.5 overflow-y-auto max-h-40 custom-scrollbar pr-1">
                                                {recipe.ingredients.map((ing, i) => {
                                                    const isMatch = isIngredientAvailable(ing);
                                                    return (
                                                        <li
                                                            key={i}
                                                            className={`flex items-start ${isMatch ? "text-gray-900 font-medium" : "text-gray-400"}`}
                                                        >
                                                            <span
                                                                className={`mr-2 ${isMatch ? "text-green-500" : "text-gray-300"}`}
                                                            >
                                                                {isMatch ? "✓" : "•"}
                                                            </span>
                                                            {ing}
                                                        </li>
                                                    );
                                                })}
                                            </ul>
                                        </div>
                                    </div>
                                ))}
                            </div>

                            {/* CONTROLES DE PAGINAÇÃO */}
                            {meta && meta.last_page > 1 && (
                                <div className="flex items-center justify-center gap-4 mt-8 bg-white p-4 rounded-xl shadow-sm border border-gray-100">
                                    <button
                                        onClick={() => fetchPage(meta.current_page - 1, ingredients)}
                                        disabled={meta.current_page === 1 || loading}
                                        className="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium shadow-sm"
                                    >
                                        Anterior
                                    </button>

                                    <span className="text-sm font-medium text-gray-600">
                                        Página <span className="text-gray-900 font-bold">{meta.current_page}</span> de{" "}
                                        <span className="text-gray-900 font-bold">{meta.last_page}</span>
                                    </span>

                                    <button
                                        onClick={() => fetchPage(meta.current_page + 1, ingredients)}
                                        disabled={meta.current_page === meta.last_page || loading}
                                        className="px-4 py-2 bg-white border border-gray-200 text-gray-700 rounded-lg hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed transition-colors font-medium shadow-sm"
                                    >
                                        Próxima
                                    </button>
                                </div>
                            )}
                        </>
                    )}
                </div>

                {/* COLUNA DIREITA - SUGESTÕES */}
                <div className="lg:col-span-1">
                    <div className="bg-white p-6 rounded-2xl shadow-sm border border-gray-100 sticky top-8">
                        <h2 className="text-lg font-bold text-gray-800 mb-4 flex items-center gap-2">
                            <span className="text-xl">⚡</span> Adição Rápida
                        </h2>
                        <div className="flex flex-wrap gap-2">
                            {COMMON_INGREDIENTS.map((item) => {
                                const isAdded = ingredients.includes(item.term);
                                return (
                                    <button
                                        key={item.term}
                                        onClick={() => !isAdded && addIngredient(item.term)}
                                        disabled={isAdded}
                                        className={`px-3 py-2 rounded-lg text-sm transition-all border ${
                                            isAdded
                                                ? "bg-green-50 text-green-600 border-green-200 cursor-default"
                                                : "bg-white text-gray-600 border-gray-200 hover:border-gray-400 hover:bg-gray-50"
                                        }`}
                                    >
                                        {isAdded ? "✓ " + item.label : "+ " + item.label}
                                    </button>
                                );
                            })}
                        </div>

                        <div className="mt-8 pt-6 border-t border-gray-100">
                            <div className="bg-blue-50 p-4 rounded-xl">
                                <h3 className="font-bold text-blue-800 text-sm mb-1">💡 Dica!</h3>
                                <p className="text-xs text-blue-600 leading-relaxed">
                                    A busca automática funciona melhor com 2 a 4 ingredientes principais. Tente combinar
                                    uma proteína (ex: Frango) com uma base (ex: Arroz).
                                </p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    );
}
