<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Feladatkezelő Projekt</title>
    @vite('resources/css/app.css')
</head>
<body class="bg-slate-100 text-slate-800 font-sans p-4 md:p-8 min-h-screen">
    
    <header class="max-w-6xl mx-auto mb-8">
        <h1 class="text-3xl font-bold text-slate-900">Feladatkezelő Dashboard</h1>
    </header>

    <main class="max-w-6xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-6">
        
        <section class="bg-white p-6 rounded-xl shadow-sm border border-slate-200 h-fit">
            <h2 class="text-xl font-semibold mb-4">Új feladat rögzítése</h2>
            <form id="taskForm" class="flex flex-col gap-4">
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Cím</label>
                    <input type="text" id="title" class="w-full border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none" required>
                </div>
                <div>
                    <label class="block text-sm font-medium text-slate-700 mb-1">Leírás</label>
                    <textarea id="description" rows="3" class="w-full border border-slate-300 rounded-lg p-2 focus:ring-2 focus:ring-blue-500 outline-none"></textarea>
                </div>
                <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Hozzáadás
                </button>
            </form>
        </section>

        <section class="md:col-span-2 flex flex-col gap-4" id="taskList">
            <p class="text-slate-500 italic">Feladatok betöltése...</p>
        </section>
    </main>

    <script>
        const API_URL = '/api/tasks';
        const CURRENT_USER_ID = 1; 

        async function fetchTasks() {
            try {
                const response = await fetch(API_URL);
                const tasks = await response.json();
                renderTasks(tasks);
            } catch (error) {
                console.error('Hiba a betöltéskor:', error);
            }
        }

        function renderTasks(tasks) {
            const list = document.getElementById('taskList');
            list.innerHTML = '';

            if(tasks.length === 0) {
                list.innerHTML = '<p class="text-slate-500 italic">Még nincsenek feladatok.</p>';
                return;
            }

            tasks.forEach(task => {
                const statusColor = task.status === 'completed' ? 'bg-green-100 text-green-800' : 'bg-amber-100 text-amber-800';
                const statusText = task.status === 'completed' ? 'Kész' : 'Folyamatban';

                list.innerHTML += `
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 hover:shadow-md transition">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">${task.title}</h3>
                            <p class="text-sm text-slate-600 mt-1">${task.description || 'Nincs részletes leírás.'}</p>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${statusColor}">
                            ${statusText}
                        </span>
                    </div>
                `;
            });
        }

        document.getElementById('taskForm').addEventListener('submit', async (e) => {
            e.preventDefault(); 
            
            const title = document.getElementById('title').value;
            const description = document.getElementById('description').value;

            try {
                const response = await fetch(API_URL, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ 
                        title: title, 
                        description: description, 
                        status: 'pending', 
                        user_id: CURRENT_USER_ID 
                    })
                });

                if (response.ok) {
                    document.getElementById('taskForm').reset(); 
                    fetchTasks(); 
                } else {
                    const errorData = await response.json();
                    alert('Hiba a validáció során: ' + JSON.stringify(errorData.errors));
                }
            } catch (error) {
                console.error('Hiba a mentéskor:', error);
            }
        });

        fetchTasks();
    </script>
</body>
</html>