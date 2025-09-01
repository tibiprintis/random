window.Preview = {
    init(el) {
        this.el = el;
        this.scene = new THREE.Scene();
        this.camera = new THREE.PerspectiveCamera(45, el.clientWidth / el.clientHeight, 0.1, 1000);
        this.renderer = new THREE.WebGLRenderer({ antialias: true });
        this.renderer.setSize(el.clientWidth, el.clientHeight);
        el.appendChild(this.renderer.domElement);
        const light = new THREE.DirectionalLight(0xffffff, 1);
        light.position.set(1, 1, 1);
        this.scene.add(light);
        const ambient = new THREE.AmbientLight(0x666666);
        this.scene.add(ambient);
        this.rotationSpeed = 0.01;
        this.autoRotate = true;
        this.meshColor = new THREE.Color('#6699ff');
        const animate = () => {
            requestAnimationFrame(animate);
            if (this.mesh && this.autoRotate) {
                this.mesh.rotation.y += this.rotationSpeed;
            }
            this.renderer.render(this.scene, this.camera);
        };
        animate();
    },
    loadSTL(url) {
        fetch(url)
            .then(r => r.arrayBuffer())
            .then(buf => {
                const geometry = this.parseBinarySTL(buf);
                const material = new THREE.MeshStandardMaterial({ color: this.meshColor });
                const mesh = new THREE.Mesh(geometry, material);
                if (this.mesh) this.scene.remove(this.mesh);
                this.mesh = mesh;
                this.scene.add(mesh);
                const box = new THREE.Box3().setFromObject(mesh);
                const size = box.getSize(new THREE.Vector3()).length();
                this.camera.position.set(0, size, size * 1.5);
                this.camera.lookAt(0, 0, 0);
            })
            .catch(() => {});
    },
    parseBinarySTL(buffer) {
        const dv = new DataView(buffer);
        const faces = dv.getUint32(80, true);
        const vertices = new Float32Array(faces * 9);
        let offset = 84;
        for (let i = 0; i < faces; i++) {
            offset += 12; // skip normal
            for (let j = 0; j < 9; j++) {
                vertices[i * 9 + j] = dv.getFloat32(offset, true);
                offset += 4;
            }
            offset += 2; // skip attribute
        }
        const geom = new THREE.BufferGeometry();
        geom.setAttribute('position', new THREE.BufferAttribute(vertices, 3));
        geom.computeVertexNormals();
        geom.center();
        return geom;
    },
    setRotationSpeed(speed) {
        this.rotationSpeed = speed;
    },
    setAutorotate(flag) {
        this.autoRotate = flag;
    },
    setMeshColor(color) {
        this.meshColor = new THREE.Color(color);
        if (this.mesh) this.mesh.material.color = this.meshColor;
    }
};
