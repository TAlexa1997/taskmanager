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
                
                <div class="border border-dashed border-slate-300 p-4 rounded-lg bg-slate-50 text-center">
                    <p class="text-sm font-medium text-slate-700 mb-2">Melléklet (Opcionális)</p>
                    
                    <video id="cameraVideo" autoplay class="hidden w-full rounded-lg mb-2 shadow-sm"></video>
                    <img id="photoPreview" class="hidden w-full rounded-lg mb-2 shadow-sm border" />
                    <canvas id="cameraCanvas" class="hidden"></canvas>

                    <button type="button" id="startCameraBtn" class="w-full bg-slate-200 text-slate-700 font-semibold py-2 px-4 rounded-lg hover:bg-slate-300 transition">
                        Kamera indítása
                    </button>
                    <button type="button" id="captureBtn" class="hidden w-full bg-green-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-green-700 transition mt-2">
                        Fotó elkészítése!
                    </button>
                </div>

                <button type="submit" class="bg-blue-600 text-white font-semibold py-2 px-4 rounded-lg hover:bg-blue-700 transition duration-200">
                    Feladat Hozzáadása
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
        let capturedImageBase64 = null;
        let videoStream = null;
        const video = document.getElementById('cameraVideo');
        const canvas = document.getElementById('cameraCanvas');
        const photoPreview = document.getElementById('photoPreview');
        const startCameraBtn = document.getElementById('startCameraBtn');
        const captureBtn = document.getElementById('captureBtn');

        startCameraBtn.addEventListener('click', async () => {
            try {
                videoStream = await navigator.mediaDevices.getUserMedia({ video: true });
                video.srcObject = videoStream;
                video.classList.remove('hidden');
                captureBtn.classList.remove('hidden');
                startCameraBtn.classList.add('hidden');
                photoPreview.classList.add('hidden');
                capturedImageBase64 = null;
            } catch (err) {
                alert('Hiba: Nem sikerült hozzáférni a kamerához. Engedélyezd a böngészőben!');
                console.error(err);
            }
        });

        captureBtn.addEventListener('click', () => {
            canvas.width = video.videoWidth;
            canvas.height = video.videoHeight;
            canvas.getContext('2d').drawImage(video, 0, 0);
            capturedImageBase64 = canvas.toDataURL('image/jpeg');
            videoStream.getTracks().forEach(track => track.stop());
            video.classList.add('hidden');
            captureBtn.classList.add('hidden');
            startCameraBtn.classList.remove('hidden');
            startCameraBtn.innerText = "Új fotó készítése";
            photoPreview.src = capturedImageBase64;
            photoPreview.classList.remove('hidden');
        });

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
                const imageHtml = task.image ? `<img src="${task.image}" class="mt-3 rounded-lg max-h-48 object-cover border shadow-sm">` : '';

                list.innerHTML += `
                    <div class="bg-white p-5 rounded-xl shadow-sm border border-slate-200 flex flex-col sm:flex-row justify-between items-start gap-4 hover:shadow-md transition">
                        <div class="w-full">
                            <h3 class="font-bold text-lg text-slate-900">${task.title}</h3>
                            <p class="text-sm text-slate-600 mt-1">${task.description || 'Nincs részletes leírás.'}</p>
                            ${imageHtml}
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider whitespace-nowrap ${statusColor}">
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
                        user_id: CURRENT_USER_ID,
                        image: capturedImageBase64 
                    })
                });

                if (response.ok) {
                    document.getElementById('taskForm').reset(); 
                    photoPreview.classList.add('hidden');
                    capturedImageBase64 = null;
                    startCameraBtn.innerText = "📷 Kamera indítása";
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