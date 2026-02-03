import { ApiResponse } from "@/types";

const API_URL = process.env.NEXT_PUBLIC_API_URL;

// Adicionamos o parâmetro page (padrão 1)
export const searchRecipes = async (ingredients: string[], page: number = 1): Promise<ApiResponse> => {
    const response = await fetch(`${API_URL}/recipes/search?page=${page}`, {
        method: "POST",
        headers: {
            "Content-Type": "application/json",
            Accept: "application/json",
        },
        body: JSON.stringify({ ingredients, page }),
    });

    if (!response.ok) {
        throw new Error("Failed to fetch recipes");
    }

    return response.json();
};
